<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Mail\BankingWireTransfertMail;
use App\Mail\PaypalConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Mockery\Exception;

use JWTAuth;
use PayPal;
use Crypt;

use App\Order;
use App\Product;
use App\Team;
use App\Configuration;

class OrderController extends Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->middleware('jwt.auth', ['except' => ['paypalDone', 'paypalCancel']]);
    $this->middleware('role:admin', ['only' => ['index']]);
  }

  /*
  creates a new order based on type

  */
  public function create(Request $request)
  {
    if ($request->get('payment_type_id') === 1)
      return $this->bankTransferPayment($request);
    else if ($request->get('payment_type_id') === 2)
      return $this->paypalPayment($request);
  }

  public function bankTransferPayment(Request $request)
  {
    $currentUser = JWTAuth::user();
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
      if (array_search(7, array_column($products, 'product_id')))
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

        array_push($products, ['product_id' => 7, 'amount' => 1]);

        $winnerTimestamp->value = 0;
        $winnerTimestamp->save();
      }

      //TODO find better way than a foreach
      foreach ($products as $product) {
        $ProductDetails = Product::findOrFail($product['product_id']);

        //test if products are all from the same event
        if ($ProductDetails->event_id !== $request->get('event_id')) {
          DB::rollback();
          return response()->json(['error' => 'Product not from the same event'], 422);
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

      if ($request->has('team')) {
        $team = Team::firstOrNew(array('name' => $request->get('team')));
        $team->save();
        $order->team()->attach($team->id, ['captain' => false, 'user_id' => $currentUser->id]);
      }
      $order->save();

      $user = $order->user()->get()[0];
      Mail::to($user->email, $user->username)->send(new BankingWireTransfertMail($user, $order, $total));
      DB::commit();
      if ($winner)
        return response()->json(['success' => 'Bank transfer subscription valid', 'state' => 'win'], 200);
      else
        return response()->json(['success' => 'Bank transfer subscription valid', 'state' => 'success'], 200);
    } catch (Throwable $e) {
      DB::rollback();
      return response()->json(['error' => 'request was well-formed but was unable to be followed due to semantic errors'], 422);
    }
  }


  public function paypalPayment(Request $request)
  {
    $currentUser = JWTAuth::user();
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


    $products = $request->get('products');
    $total = 0;
    $nbSubscription = 0;
    $winner = false;

    if (array_search(7, array_column($products, 'product_id')))
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

      array_push($products, ['product_id' => 7, 'amount' => 1]);

      $winnerTimestamp->value = 0;
      $winnerTimestamp->save();
    }

    $payer = PayPal::Payer();
    $payer->setPaymentMethod('paypal');

    $itemList = PayPal::ItemList();
    //TODO find better way than a foreach
    foreach ($products as $product) {
      $ProductDetails = Product::findOrFail($product['product_id']);

      //test if products are all from the same event
      if ($ProductDetails->event_id !== $request->get('event_id')) {
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
      $response = $payment->create($this->_apiContext);
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
        $total += $ProductDetails->price * $product['amount'];
      }

      if (array_key_exists('team', $data)){
        $team = Team::firstOrNew(array('name' => $data['team']));
        $team->save();
        $order->team()->attach($team->id, ['captain' => false, 'user_id' => $data['user_id']]);
      }
      $order->save();

      $order->state = 1;
      $order->paypal_paymentId = $id;
      $order->save();

      DB::commit();
      $payment = PayPal::getById($id, $this->_apiContext);
      $executePayment = $payment->execute($paymentExecution, $this->_apiContext);

      $win = $order->products()->where('product_id', 7)->count();
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

}
