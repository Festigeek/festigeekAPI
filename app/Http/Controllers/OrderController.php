<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

use Crypt;
use Validator;
use DateTime;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;

use App\Mail\TeamOwnerMail;
use App\Mail\BankingWireTransfertMail;
use App\Mail\PaypalConfirmation;

use App\Order;
use App\Product;
use App\Team;
use App\Configuration;

class OrderController extends Controller
{
    // WARNING To adapt each year !
    private const EVENT_YEAR = 2018;
    private const BURGER_ID = 14;
    private const FREE_BURGER_ID = 16;

    private $apiContext;
    private $client_id;
    private $secret;
    private $settings;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api', ['except' => ['paypalDone', 'paypalCancel']]);
        $this->middleware('role:admin|comite', ['only' => ['index', 'patch', 'consumeProduct']]);
        $this->middleware('role:admin', ['only' => ['delete']]);

        $this->settings = config('paypal.settings');
        // Detect if we are running in live mode or sandbox
        if(config('paypal.settings.mode') == 'live'){
            $this->client_id = config('paypal.live_client_id');
            $this->secret = config('paypal.live_secret');
            $this->settings['service.EndPoint'] = config('paypal.live_end_point');
        } else {
            $this->client_id = config('paypal.sandbox_client_id');
            $this->secret = config('paypal.sandbox_secret');
            $this->settings['service.EndPoint'] = config('paypal.sandbox_end_point');
        }

        // Set the Paypal API Context/Credentials
        $this->apiContext = new ApiContext(new OAuthTokenCredential($this->client_id, $this->secret));
        $this->apiContext->setConfig($this->settings);
    }

    private function getCSV(Collection $orders)
    {
        $datetime = new DateTime();
        $headers = [
            'Numéro de commande',
            'Nom',
            'Prénom',
            'Nom d\'utilisateur',
            'E-mail',
            'Pseudo Steam',
            'Pseudo Riot',
            'Battle TAG',
            'Nom de Team',
            'Participation tournoi principal',
            'Mineur',
            'Burgers',
            'Burgers gratuits',
            'Petit déjs',
            'Montant total',
            'Moyen de paiement',
            'Paypal ID',
            'Statut paiement',
            'Date de la commande',
            'Etudiants',
            'Présent'
        ];

        return Excel::create("orders_" . $datetime->format('Y_m_d_His'), function($excel) use ($datetime, $headers, $orders) {
            $excel->sheet("feuille_1", function($sheet) use ($headers, $orders) {

                $sheet->appendRow($headers);
                $orders->each(function ($item) use ($sheet) {

                    $age = ((new DateTime($item->user->birthdate))->diff(new DateTime()))->y;
                    $total = $item->products->filter(function($value) { return !is_null($value); })
                        ->sum(function($value) { return $value->pivot->amount * $value->price; });
                    $tournoi = $item->products->first(function($val){ return $val->product_type_id == 1; });
                    $burgers = $item->products->first(function($val){ return $val->id == 5; });
                    $dejs = $item->products->first(function($val){ return $val->id == 6; });
                    $bGratuits = $item->products->first(function($val){ return $val->id == self::FREE_BURGER_ID; });

                    $sheet->appendRow([
                        "XX" . $item->id . "XX (ID# $item->id)", // Numéro de commande

                        $item->user->lastname, // Nom
                        $item->user->firstname, // Prénom
                        $item->user->username, // Nom d'utilisateur
                        $item->user->email, // E-mail
                        $item->user->steamID64.'', // Pseudo Steam
                        $item->user->lol_account, // Pseudo Riot
                        $item->user->battleTag, // Battle TAG

                        !is_null($item->team) ? $item->team->name : '', // Nom de Team
                        $tournoi->name, // Participation tournoi principal
                        ($age<18)?'oui':'non',// Mineur
                        !is_null($burgers) ? $burgers->pivot->amount : '', // Burger
                        !is_null($bGratuits) ? $bGratuits->pivot->amount : '', // Burger gratuit
                        !is_null($dejs) ? $dejs->pivot->amount : '', // Petit déj
                        number_format($total, 2, '.', ''), // Montant total
                        $item->payment_type->name, // Moyen de paiement
                        $item->paypal_paymentID, // Paypal ID
                        $item->state, // Statut paiement
                        $item->created_at
                    ]);
                });

            });
        });
    }

    // CRUD

    /**
     * Show all orders.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $eventId = ($request->filled('eventId')) ? $request->get('eventId') : null;
        $orders = Order::all();
        if(!is_null($eventId))
            $orders = $orders->filter(function($order) use($eventId) {
                return $order->event_id === $eventId;
            });

        $format = ($request->filled('format')) ? $request->get('format') : 'json';

        switch ($format) {
            case 'txt':
            case 'xls':
                return response()->json(['error' => 'Unsupported format.']);
                break;
            case 'csv':
                $this->getCSV($orders)->download('csv');
                break;
            case 'json':
            default:
                return response()->json($orders);
        }
    }

    /**
     * Show a specific order.
     *
     * @param Request $request
     * @param $order_id
     *
     * @return Response
     */
    public function show(Request $request, $order_id)
    {
        try {
            $order = Order::findOrFail($order_id);
            $user = $order->user()->first()->makeVisible(['QRCode']);

        }
        catch(Exception $e) {
            return response()->json(['error' => 'Order and/or User not found.'], 404);
        }

        if($this->isAdminOrOwner($order->user_id)) {
            $format = $request->has('format') ? $request->get('format') : 'json';
            switch ($format) {
                case 'pdf':
                    $html =  view('pdf.ticket', ['order' => $order, 'user' => $user]);
                    return \PDF::loadHTML($html)->setPaper('a4')->setOption('margin-bottom', 0)->inline('ticket_lan.pdf');
                    break;
                case 'json':
                default:
                    return response()->json($order);
            }
        }
        else abort(403);
    }

    /**
     * Creates a new order based on type
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $event_id = $request->get('event_id');
            if (is_null($event_id))
                return response()->json(['error' => 'add event id']);

            if (Auth::user()->orders()->where('event_id', $request->get('event_id'))->get()->isNotEmpty())
                return response()->json(['error' => 'You have already created an order for this event'], 422);

            if (!$request->filled('checked_legal'))
                return response()->json(['error' => 'Please accept the general sales conditions'], 422);

            if (!$request->filled('products'))
                return response()->json(['error' => 'Please select products'], 422);

            // Get products in DB
            $products = collect($request->get('products'))->transform(function ($item) {
                $item['data'] = Product::find($item['product_id']);
                return $item;
            });

            // Test if all product are for the announced event
            if ($products->contains(function ($item) use ($event_id) {
                return is_null($item['data']) || $item['data']->event_id != $event_id;
            }))
                return response()->json(['error' => "One or more products doesn't exists or are from the wrong event"], 422);

            // Test if there is only one tournament subscription AND if they are still available
            $outOfStock = [];
            if ($products->reduce(function ($acc, $item) use (&$outOfStock) {
                    if ($item['data']->quantity_max != null && $item['data']->sales() >= $item['data']->quantity_max)
                        $outOfStock[] = $item['data']->description;
                    return ($item['data']->product_type_id === 1) ? $acc + $item['amount'] : $acc;
                }) > 1)
                return response()->json(['error' => "More than one inscription"], 422);

            // Message for out-of-stock condition
            if (count($outOfStock))
                return response()->json(['error' => "No more tickets available for the next items", "infos" => $outOfStock], 422);

            // Check if free burger (contest) have been added by forging request
            if ($products->pluck('product_id')->contains(self::FREE_BURGER_ID))
                return response()->json(['error' => 'Go fuck yourself'], 418);

            // Create order
            $order = Order::create([
                'user_id' => Auth::user()->id,
                'event_id' => $event_id,
                'payment_type_id' => $request->get('payment_type_id'),
                'data' => json_encode($request->all())
            ]);

            // Check teams
            $teamMngm = $this->manageTeam($request, $order);
            if($teamMngm['error'])
                return response()->json($teamMngm['infoError'], 422);

            // Go on specific payment method
            switch ($request->get('payment_type_id')) {
                case 1:
                    return $this->bankTransferPayment($order, $products, $this->burgerContest($products));
                case 2:
                    return $this->paypalPayment($order, $products, $this->burgerContest($products));
                default:
                    return response()->json(['error' => 'Unknown payment method']);
            }
        }
        catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['error' => 'Request was well-formed but was unable to be followed due to semantic errors'], 422);
        }
    }

    private function burgerContest(Collection &$products)
    {
        $winner = false;

        $winnerTimestamp = Configuration::where('name', "timestamp-winner-" . self::EVENT_YEAR)->first();

        if (!is_null($winnerTimestamp) && time() > $winnerTimestamp->value && $winnerTimestamp->value != 0) {
            //check if the user has ordered a burger (in that case we subtract a burger)
            $index = $products->search(function ($item) {
                return $item['product_id'] == self::BURGER_ID;
            });

            if($index) {
                $burgers = $products->get($index);
                $burgers['amount'] -= 1;
                if ($burgers['amount'] == 0)
                    $products->forget($index);
                else
                    $products->put($index, $burgers);
            }

            $products->push(['product_id' => self::FREE_BURGER_ID, 'amount' => 1, 'data' => Product::find(self::FREE_BURGER_ID)]);
            $winnerTimestamp->value = 0;
            $winnerTimestamp->save();
            $winner = true;
        }

        return $winner;
    }

    private function manageTeam(Request $request ,Order $order)
    {
        $result = ['error' => false];
        if ($request->filled('team_code')) {
            $result['team'] = Team::where('code', '=', $request->get('team_code'))->first();
            if(is_null($result['team'])){
                $result['error'] = true;
                $result['infoError'] = ['error' => 'Wrong team code'];
                DB::rollback();
                return $result;
            }
            $order->team()->attach($result['team']->id, ['captain' => false, 'user_id' => Auth::user()->id]);
        }
        else if ($request->filled('team')) {
            if(!is_null(Team::where('alias', '=', Team::generateAlias($request->get('team')))->first())) {
                $result['error'] = true;
                $result['infoError'] = ['error' => 'Team already exists.'];
                DB::rollback();
                return $result;
            }
            $result['team'] = Team::create(['name' => $request->get('team')]);
            $result['team']->save();
            $order->team()->attach($result['team']->id, ['captain' => true, 'user_id' => Auth::user()->id]);
            $order->save();
        }
        else {
            if($order->products()->get()->contains('need_team', 1)) {
                $result['error'] = true;
                $result['infoError'] = ['error' => 'You need a team !'];
                DB::rollback();
                return $result; 
            }
        }
        return $result;
    }

    private function cancelOrder($order) {
        if(!is_null($order)) {
            $user = $order->user()->first();
            $team = $order->team()->first();

            if (!is_null($team)) {
                if ($team->captain->id == $user->id)
                    $team->delete();
                else
                    $team->users()->detach($user->id);
            }

            $order->delete();
        }
    }

    // Bank & PayPal Stuff

    public function bankTransferPayment(Order $order, Collection $products, $winner = false)
    {
        $products->each(function($product) use($order, &$total) {
            $product['data']->sold += $product['amount'];
            $product['data']->save();
            $order->products()->save($product['data'], ['amount' => $product['amount']]);
        });

        $user = $order->user()->first();
        $team = $order->team()->first();
        if(!is_null($team) && $team->captain->id == Auth::user()->id)
            Mail::to($user->email, $user->username)->send(new TeamOwnerMail($user, $team));

        Mail::to($user->email, $user->username)->send(new BankingWireTransfertMail($user, $order));

        // Save info only if mail have been sent correctly
        DB::commit();

        return response()->json(['success' => 'Bank transfer subscription valid', 'state' => ($winner) ? 'win' : 'success'], 200);
    }

    public function paypalPayment(Order $order, Collection $products, $winner = false)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $itemList = new ItemList();

        $products->each(function($product) use($order, &$itemList) {
            $item = new Item();
            $item->setName($product['data']->name)
                ->setCurrency('CHF')
                ->setQuantity($product['amount'])
                ->setPrice($product['data']->price);
            $itemList->addItem($item);

            $product['data']->sold += $product['amount'];
            $product['data']->save();
            $order->products()->save($product['data'], ['amount' => $product['amount']]);
        });

        $data['order_id'] = $order->id;
        $cryptData = Crypt::encrypt($data);

        $amount = new Amount();
        $amount->setCurrency('CHF');
        $amount->setTotal($order->total);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setItemList($itemList);
        $transaction->setDescription('Payment description');
        $transaction->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(action('OrderController@paypalDone', ['data' => $cryptData]));
        $redirectUrls->setCancelUrl(action('OrderController@paypalCancel', ['data' => $cryptData]));

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));

        try {
            $response = $payment->create($this->apiContext);
            $redirectUrl = $response->links[1]->href;

            // Save info only if Paypal request have been sent correctly
            DB::commit();

            return response()->json(['success' => 'Ready for PayPal transaction', 'link' => $redirectUrl], 200);
        }
        catch (Exception $e) {
            return response()->json(['error' => 'paypal error'], 200);
        }
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function paypalDone(Request $request)
    {
        try {
            $id = $request->get('paymentId');
            $payer_id = $request->get('PayerID');

            $paymentExecution = new PaymentExecution();
            $paymentExecution->setPayerId($payer_id);

            $data = Crypt::decrypt($request->get('data'));
            $order = Order::findOrFail($data['order_id']);

            DB::beginTransaction();

            $order->state = 1;
            $order->paypal_paymentId = $id;
            $order->save();

            // Executing payment... I think...
            $payment = Payment::get($id, $this->apiContext);
            $payment->execute($paymentExecution, $this->apiContext);

            DB::commit();

            $win = $order->products()->where('product_id', self::FREE_BURGER_ID)->count() > 0;
            $user = $order->user()->first();
            $team = $order->team()->first();

            if(!is_null($team) && $team->captain->id === $user->id)
                Mail::to($user->email, $user->username)->send(new TeamOwnerMail($user, $team));

            Mail::to($user->email, $user->username)->send(new PaypalConfirmation($user, $order, $order->total));

            $url = ($win) ? 'https://www.festigeek.ch/#!/checkout?state=win' : 'https://www.festigeek.ch/#!/checkout?state=success';
            return redirect($url);

        }
        catch (Exception $e) {
            DB::rollback();

            // If something goes wrong, soft delete everything
            $this->cancelOrder($order);

            return redirect('https://www.festigeek.ch/#!/checkout?state=error');
        }
    }

    public function paypalCancel(Request $request)
    {
        $data = Crypt::decrypt($request->get('data'));

        try {
            $order = Order::findOrFail($data['order_id']);
            // If something goes wrong, soft delete everything
            $this->cancelOrder($order);

            return redirect('https://www.festigeek.ch/#!/checkout?state=cancelled');
        }
        catch (Exception $e) {
            return redirect('https://www.festigeek.ch/#!/checkout?state=error');
        }
    }

    /**
     * Update an order
     * @param Request $request
     * @param Integer $order_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function patch(Request $request, $order_id)
    {
        try {
            $order = Order::findOrFail($order_id);
        }
        catch (\Exception $e) {
            return response()->json(['error' => 'Order not Found'], 404);
        }

        $inputs = $request->only([
            'state',
            'code_lan'
        ]);

        $validator = Validator::make($inputs, [
            'state' => 'nullable|numeric',
            'code_lan' => 'nullable|string',
        ]);

        if ($validator->fails())
            return response()->json(['error' => 'Validation error.', 'validation' => $validator], 400);

        if($request->has("state"))
            $order->state =$request->get("state");
        if($request->has("code_lan"))
            $order->code_lan = $request->get("code_lan");

        $order->save();
        return response()->json($order);
    }

    /**
     * Delete an order
     * @param Integer $order_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($order_id)
    {
        // Role:admin middleware, no stress boyz ;)
        $order = Order::find($order_id);

        if(is_null($order))
            return response()->json(['error' => 'Order not Found'], 404);

        $order->products()->get()->each(function($product) {
            $product->sold -= $product->pivot->amount;
            $product->save();
        });

        $order->products()->delete();
        $order->delete();

        return response()->json(['success']);
    }

    /**
     * Get the team associate to an order
     * @param Integer $order_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeam($order_id) {
        $order = Order::find($order_id);
        if(!is_null($order)) {
            if(is_null($order->team))
                return response()->json(['error' => 'No team found for this order'], 404);
                
            $team = (auth()->user() && $order->team->hasUser(auth()->user()->id)) ? $order->team->makeVisible('code') : $order->team;
            return response()->json($team, 200);
        }
        else
            return response()->json(['error' => 'Order not Found'], 404);
    }
}
