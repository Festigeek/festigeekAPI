<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Mail\BankingWireTransfertMail;
use App\Mail\PaypalConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;

use Auth;
use Crypt;
use DateTime;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Validator;

use App\Order;
use App\Product;
use App\Team;
use App\Configuration;

class OrderController extends Controller
{
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

//        $this->apiContext = PayPal::ApiContext($this->client_id, $this->secret);
//
//        $this->apiContext->setConfig(array(
//            'mode' => config('services.paypal.mode'),
//            'service.EndPoint' => config('services.paypal.end_point'),
//            'http.ConnectionTimeOut' => 60,
//            'log.LogEnabled' => true,
//            'log.FileName' => storage_path('logs/paypal.log'),
//            'log.LogLevel' => 'FINE'
//        ));

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
                    $bGratuits = $item->products->first(function($val){ return $val->id == 15; });

                    $sheet->appendRow([
                        "20" . $item->id . "13 (ID# $item->id)", // Numéro de commande

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
        $orders = Order::all();
        $format = ($request->has('format')) ? $request->get('format') : 'json';

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
        else {

        }
    }

    /**
     * Creates a new order based on type
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        if ($request->get('payment_type_id') === 1)
            return $this->bankTransferPayment($request);
        else if ($request->get('payment_type_id') === 2)
            return $this->paypalPayment($request);
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
    public function delete($order_id){
        DB::beginTransaction();

        try{
            $order = Order::find($order_id);
            foreach($order->products()->get() as $product) {
                $product->sold -= $product->pivot->amount;
                $product->save();
            }

            $order->products()->detach();
            $order->delete();

            DB::commit();
            return response()->json(['success']);
        }
        catch (\Throwable $e) {
            DB::rollback();

            return response()->json(['error'=>$e]);
        }
    }

    // Others

    public function getTeam($order_id) {
        return Order::find($order_id)->team()->get();
    }

    // PayPal Stuff

    //TODO rewrite this part cuz I lost my sight & got brain cancer after reading it to try to understand it
    public function bankTransferPayment(Request $request)
    {
        $currentUser = Auth::user();
        $data = $request->all();
        $data['user_id'] = $currentUser->id;

        //check event_id
        if (!$request->has('event_id'))
            return response()->json(['error' => 'add event id']);

        //TODO with eloquent
        //check if user already registered a payment order with event_id to his name
        $existingPayment = $currentUser->orders()->where('event_id', $data['event_id'])->get();
        if ($existingPayment->isNotEmpty())
            return response()->json(['error' => 'You have already created an order for this event']);

        if (!$request->has('checked_legal') || !$request->get('checked_legal'))
            return response()->json(['error' => 'Please accept the general sales conditions']);

        if (!$request->has('products'))
            return response()->json(['error' => 'Please select products']);

        try {
            DB::beginTransaction();

            $order = Order::create($data);
            $order->data = json_encode($request->all());
            $products = $request->get('products');
            $total = 0;
            $nbSubscription = 0;
            $winner = false;

            //TODO add form data in 'data' field
            //TODO add product_type => bon
            //TODO WARNING Hard-coded IDs
            if (array_search(15, array_column($products, 'product_id')))
                return response()->json(['error' => 'Go fuck yourself'], 422);

            $winnerTimestamp = Configuration::where('name', 'winner-timestamp')->first();

            if (time() > $winnerTimestamp->value && $winnerTimestamp->value != 0) {
                $winner = true;
                //check if the user has order a burger (in that case we subtract a burger)
                $key = array_search(5, array_column($products, 'product_id'));

                if ($key) {
                    --$products[$key]['amount'];
                    if ($products[$key]['amount'] == 0)
                        unset($products[$key]);
                }

                array_push($products, ['product_id' => 15, 'amount' => 1]);

                $winnerTimestamp->value = 0;
                $winnerTimestamp->save();
            }

            //TODO find better way than a foreach
            foreach ($products as $product) {

                $ProductDetails = Product::findOrFail($product['product_id']);
                //test if products are all from the same event
              //  dd($ProductDetails->event_id, $request->get('event_id'));


                if ($ProductDetails->event_id != $request->get('event_id')) {
                    DB::rollback();
                    return response()->json(['error' => 'Product not from the same event', "product details id" => $ProductDetails->event_id, "request id" => $request->get('event_id')], 422);
                }


                //we find an subscription
                if ($ProductDetails->product_types_id === 1) {
                    ++$nbSubscription;
                    //test if there is not more then 1 subscription
                    if ($nbSubscription > 1 || $product['amount'] > 1) {
                        DB::rollback();
                        return response()->json(['error' => 'More than one inscription'], 422);
                    }
                }

                //here test if the items are available
                if ($ProductDetails->quantity_max != null && $ProductDetails->sold >= $ProductDetails->quantity_max) {
                    DB::rollback();
                    return response()->json(['error' => 'No more tickets available'], 422);
                }

                $ProductDetails->sold += $product['amount'];
                $ProductDetails->save();
                $order->products()->save($ProductDetails, ['amount' => $product['amount']]);
                $total += $ProductDetails->price * $product['amount'];
            }

            // Check Teams
            $team = null;
            if ($request->has('team_code')) {
                $team = Team::where('code', '=', $request->get('team_code'))->first();
                if(is_null($team)){
                    DB::rollback();
                    return response()->json(['error' => 'Wrong team code'], 422);
                }
                $order->team()->attach($team->id, ['captain' => false, 'user_id' => $currentUser->id]);
            }
            else if ($request->has('team')) {
                if(!is_null(Team::where('alias', '=', Team::generateAlias($request->get('team')))->first())) {
                    DB::rollback();
                    return response()->json(['error' => 'Team already exists.'], 422);
                }
                $team = Team::create(array('name' => $request->get('team')));
                $team->save();
                $order->team()->attach($team->id, ['captain' => true, 'user_id' => $currentUser->id]);
                $order->save();
            }

            $user = $order->user()->get()[0];
            //TODO Create mail template to send team code
//            if(!is_null($team) && $team->captain->id == $currentUser->id)
//                Mail::to($user->email, $user->username)->send(new TeamOwnerMail($user, $team));
            Mail::to($user->email, $user->username)->send(new BankingWireTransfertMail($user, $order, $total));
            DB::commit();
            if ($winner)
                return response()->json(['success' => 'Bank transfer subscription valid', 'state' => 'win'], 200);
            else
                return response()->json(['success' => 'Bank transfer subscription valid', 'state' => 'success'], 200);
        } catch (Throwable $e) {
            DB::rollback();
            return response()->json(['error' => 'Request was well-formed but was unable to be followed due to semantic errors'], 422);
        }
    }

    public function paypalPayment(Request $request)
    {
        $currentUser = Auth::user();
        $data = $request->all();
        $data['user_id'] = $currentUser->id;

        // screw you greendragon18
        if (!$request->has('event_id'))
            return response()->json(['error' => 'add event id']);

        //TODO with eloquent
        //check if user already registered a payment order with event_id to his name
        $existingPayment = $currentUser->orders()->where('event_id', $data['event_id'])->get();
        if ($existingPayment->isNotEmpty()) // TODO: Check if also valid
            return response()->json(['error' => 'You have already created an order for this event']);

        if (!$request->has('checked_legal') || !$request->get('checked_legal'))
            return response()->json(['error' => 'Please accept the general sales conditions']);

        if (!$request->has('products'))
            return response()->json(['error' => 'Please select products']);

        $products = $request->get('products');
        $total = 0;
        $nbSubscription = 0;
        $winner = false;

        if (array_search(15, array_column($products, 'product_id')))
            return response()->json(['error' => 'Go fuck yourself'], 418);

        $winnerTimestamp = Configuration::where('name', 'winner-timestamp')->first();

        if (time() > $winnerTimestamp->value && $winnerTimestamp->value != 0) {
            $winner = true;
            //check if the user has order a burger (in that case we subtract a burger)
            $key = array_search(5, array_column($products, 'product_id'));

            if ($key) {
                --$products[$key]['amount'];
                if ($products[$key]['amount'] == 0)
                    unset($products[$key]);
            }

            array_push($products, ['product_id' => 15, 'amount' => 1]);

            $winnerTimestamp->value = 0;
            $winnerTimestamp->save();
        }

        $payer = PayPal::Payer();
        $payer->setPaymentMethod('paypal');

        $itemList = PayPal::ItemList();

        foreach ($products as $product) {
            $ProductDetails = Product::findOrFail($product['product_id']);

            //test if products are all from the same event
            if ($ProductDetails->event_id != $request->get('event_id')) {
                return response()->json(['error' => 'Product not from the same event'], 422);
            }

            //we find an subscription
            if ($ProductDetails->product_types_id === 1) {
                ++$nbSubscription;
                //test if there is not more then 1 subscription
                if ($nbSubscription > 1 || $product['amount'] > 1) {
                    return response()->json(['error' => 'More than one inscription'], 422);
                }
            }

            //here test if the items are available
            if ($ProductDetails->quantity_max != null && $ProductDetails->sold >= $ProductDetails->quantity_max) {
                return response()->json(['error' => 'No more tickets available'], 422);
            }

            $item = PayPal::Item();
            $item->setName($ProductDetails->name)
                ->setCurrency('CHF')
                ->setQuantity($product['amount'])
                ->setPrice($ProductDetails->price);
            $itemList->addItem($item);

            $total += $ProductDetails->price * $product['amount'];
        }

        $data['products'] = $products;

        $amount = PayPal::Amount();
        $amount->setCurrency('CHF');
        $amount->setTotal($total);

        $transaction = PayPal::Transaction();
        $transaction->setAmount($amount);
        $transaction->setItemList($itemList);
        $transaction->setDescription('Payment description');
        $transaction->setInvoiceNumber(uniqid());


        $redirectUrls = PayPal:: RedirectUrls();

        $cryptData = Crypt::encrypt($data);
        $cryptTotal = Crypt::encrypt($total);
        $redirectUrls->setReturnUrl(action('OrderController@paypalDone', ['data' => $cryptData, 'total' => $cryptTotal]));
        $redirectUrls->setCancelUrl(action('OrderController@paypalCancel'));


        $payment = PayPal::Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));

        try {
            $response = $payment->create($this->apiContext);
            $redirectUrl = $response->links[1]->href;

            return response()->json(['success' => 'Ready for PayPal transaction', 'link' => $redirectUrl], 200);
        } catch (Exception $ex) {
            return response()->json(['error' => 'paypal error'], 200);
        }
    }

    public function paypalDone(Request $request)
    {
        $id = $request->get('paymentId');
        $payer_id = $request->get('PayerID');

        $paymentExecution = PayPal::PaymentExecution();
        $paymentExecution->setPayerId($payer_id);

        $data = Crypt::decrypt($request->get('data'));
        $total = Crypt::decrypt($request->get('total'));
        try {
            DB::beginTransaction();
            $order = Order::create($data);
            $order->data = json_encode($data);
            $products = $data['products'];

            foreach ($products as $product) {
                $ProductDetails = Product::findOrFail($product['product_id']);

                //here test if the items are available
                if ($ProductDetails->quantity_max != null && $ProductDetails->sold >= $ProductDetails->quantity_max) {
                    DB::rollback();
                    return response()->json(['error' => 'No more tickets available'], 422);
                }

                $ProductDetails->sold += $product['amount'];
                $ProductDetails->save();
                $order->products()->save($ProductDetails, ['amount' => $product['amount']]);
                // $total += $ProductDetails->price * $product['amount'];
            }

            // Check Teams
            $team = null;
            if (array_key_exists('team_code', $data)) {
                $team = Team::where('code', '=', $data['team_code'])->first();
                if(is_null($team)){
                    DB::rollback();
                    return response()->json(['error' => 'Wrong team code'], 422);
                }
                $order->team()->attach($team->id, ['captain' => false, 'user_id' => $data['user_id']]);
            }
            else if (array_key_exists('team', $data)) {
                if(!is_null(Team::where('alias', '=', Team::generateAlias($data['team']))->first())) {
                    DB::rollback();
                    return response()->json(['error' => 'Team already exists.'], 422);
                }
                $team = Team::create(array('name' => $data['team']));
                $team->save();
                $order->team()->attach($team->id, ['captain' => true, 'user_id' => $data['user_id']]);
            }
            $order->save();

//            if (array_key_exists('team', $data)){
//                $team = Team::firstOrNew(array('name' => $data['team']));
//                $team->save();
//                $order->team()->attach($team->id, ['captain' => false, 'user_id' => $data['user_id']]);
//            }
//            $order->save();

            $order->state = 1;
            $order->paypal_paymentId = $id;
            $order->save();

            DB::commit();
            $payment = PayPal::getById($id, $this->apiContext);
            $executePayment = $payment->execute($paymentExecution, $this->apiContext);

            $win = $order->products()->where('product_id', 15)->count();
            $user = $order->user()->get()[0];
            Mail::to($user->email, $user->username)->send(new PaypalConfirmation($user, $order, $total));
            if ($win)
                return redirect('https://www.festigeek.ch/#!/checkout?state=win');
            else
                return redirect('https://www.festigeek.ch/#!/checkout?state=success');
        } catch (Exception $ex) {
            DB::rollback();
            return redirect('https://www.festigeek.ch/#!/checkout?state=error');
        }
    }

    public function paypalCancel(Request $request)
    {
        return redirect('https://www.festigeek.ch/#!/checkout?state=cancelled');
    }

    public function consumeProduct(Request $request, $order_id, $product_id)
    {
        try {
            $order = Order::findOrFail($order_id);
        }
        catch (\Exception $e) {
            return response()->json(['error' => 'Order not Found'], 404);
        }

        $inputs = $request->only(['consume']);

        $validator = Validator::make($inputs, [
            'consume' => 'required|numeric'
        ]);

        if ($validator->fails())
            return response()->json(['error' => 'Validation error.', 'validation' => $validator], 400);

        $consume = $request->get("consume");
        $product = $order->products()->where('product_id', $product_id)->first();

        if(intval($consume) <= $product->pivot->amount) {
            $order->products()->updateExistingPivot((int)$product_id, ['consume' => intval($consume)]);
        }
        else
            return response()->json(['error' => 'Try to consume more then ordered.', 'validation' => $validator], 400);

        return response()->json($order);
    }
}
