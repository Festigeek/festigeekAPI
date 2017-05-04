<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Mockery\Exception;
use PayPal;
use Crypt;
use App\Order;
use App\Product;
use App\Team;
use Illuminate\Support\Facades\DB;

use App\Mail\BankingWireTransfertMail;
use App\Mail\PaypalConfirmation;
use Illuminate\Support\Facades\Mail;

use JWTAuth;

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
    $currentUser = JWTAuth::user();
    $data = $request->all();
    $data['user_id'] = $currentUser->id;


    //check event_id
	  if(!$request->has('event_id'))
	    return response()->json(['error'=>'add event id']);

    //TODO with eloquent
    //check if user already registered a payment order with event_id to his name
    $existingPayment = $currentUser->orders()->where('event_id', $data['event_id'])->get();
    if($existingPayment->isNotEmpty())
      return response()->json(['error'=>'You have already created an order for this event']);

    if(!$request->has('checked_legal') || !$request->get('checked_legal'))
	    return response()->json(['error'=>'Please accept the general sales conditions']);

    if(!$request->has('products'))
	    return response()->json(['error'=>'Please select products']);

//    DB::beginTransaction();
    try{
	    DB::beginTransaction();

	    $order = Order::create($data);
	    $products = $request->get('products');
		  $total = 0;
		  $nbSubscription = 0;

		  //TODO find better way than a foreach
		  foreach ($products as $product){
			  $ProductDetails = Product::findOrFail($product['product_id']);

			  //test if products are all from the same event
			  if ($ProductDetails->event_id !== $request->get('event_id')) {
				  DB::rollback();
				  return response()->json(['error' => 'Product not from the same event'], 422);
			  }

			  //we find an subscription
			  if($ProductDetails->product_types_id === 1) {
          ++$nbSubscription;
          //test if there is not more then 1 subscription
          if($nbSubscription > 1 || $product['amount'] > 1) {
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

		  if($request->has('team')) {
        $team = Team::firstOrNew(array('name' => $request->get('team')));
        $team->save();
        $order->team()->attach($team->id, ['captain' => false, 'user_id' => $currentUser->id]);
      }

      //test if paypal or bank transfert Here
      if($request->get('payment_type_id') === 2){
        $payer = PayPal::Payer();
        $payer->setPaymentMethod('paypal');

        $itemList = PayPal::ItemList();
        foreach($order->products()->get() as $product){
          $item = PayPal::Item();
          $item->setName($product->name)
            ->setCurrency('CHF')
            ->setQuantity($product->pivot->amount)
            ->setPrice($product->price);
          $itemList->addItem($item);
        }

        $amount = PayPal::Amount();
        $amount->setCurrency('CHF');
        $amount->setTotal($total);

        $transaction = PayPal::Transaction();
        $transaction->setAmount($amount);
        $transaction->setItemList($itemList);
        $transaction->setDescription('Payment description');
        $transaction->setInvoiceNumber(uniqid());


        $redirectUrls = PayPal:: RedirectUrls();

        $cryptOrderID = Crypt::encrypt($order->id);
        $cryptTotal = Crypt::encrypt($total);
        $redirectUrls->setReturnUrl(action('OrderController@paypalDone', ['order' => $cryptOrderID, 'total' => $cryptTotal]));
        $redirectUrls->setCancelUrl(action('OrderController@paypalCancel', ['order' => $cryptOrderID]));


        $payment = PayPal::Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));

        try{
          $response = $payment->create($this->_apiContext);
          $redirectUrl = $response->links[1]->href;
          DB::commit();

          return response()->json(['success' => 'Ready for PayPal transaction', 'link' =>$redirectUrl], 200);
        } catch (Exception $ex) {
          return response()->json(['error' => 'paypal error'], 200);
        }
      } else if ($request->get('payment_type_id') === 1){

        $user = $order->user()->get()[0];
        Mail::to($user->email, $user->username)->send(new BankingWireTransfertMail($user, $order, $total));
        DB::commit();
        return response()->json(['success' => 'Subscription valid'], 200);
      }
    }catch (Exception $e) {
      DB::rollback();
      return response()->json(['error'=>'request was well-formed but was unable to be followed due to semantic errors'], 422);
    }
//    }catch (\Throwable $e) {
//      DB::rollback();
//      return response()->json(['error'=>'request was well-formed but was unable to be followed due to semantic errors'], 422);
//    }
  }

    public function paypalDone(Request $request)
    {
      $id = $request->get('paymentId');
      $payer_id = $request->get('PayerID');

      $paymentExecution = PayPal::PaymentExecution();
      $paymentExecution->setPayerId($payer_id);

      $order = Order::find(Crypt::decrypt($request->get('order')));
      $total = Crypt::decrypt($request->get('total'));
      try {
        $payment = PayPal::getById($id, $this->_apiContext);
        $executePayment = $payment->execute($paymentExecution, $this->_apiContext);

        $order->state = 1;
        $order->paypal_paymentId = $id;
        $order->save();

        $user = $order->user()->get()[0];
        Mail::to($user->email, $user->username)->send(new PaypalConfirmation($user, $order, $total));
//        return response()->json(['success' => 'payment success'], 200);
        return redirect('https://www.festigeek.ch/#!/checkout?state=success');
      } catch (Exception $ex){
        DB::beginTransaction();
        try{
          $order = Order::find(Crypt::decrypt($request->get('order')));
          foreach($order->products()->get() as $product){
            $product->sold -= $product->pivot->amount;
            $product->save();
          }

          $order->products()->detach();
          $order->delete();

          DB::commit();
          return redirect('https://www.festigeek.ch/#!/checkout?state=error');
        }catch (\Throwable $e) {
          DB::rollback();
          return redirect('https://www.festigeek.ch/#!/checkout?state=error');
        }
      }
    }

    public function paypalCancel(Request $request)
    {
      DB::beginTransaction();
      try{
        $order = Order::find(Crypt::decrypt($request->get('order')));
        foreach($order->products()->get() as $product){
          $product->sold -= $product->pivot->amount;
          $product->save();
        }

        $order->products()->detach();
        $order->delete();

        DB::commit();
        return redirect('https://www.festigeek.ch/#!/checkout?state=cancelled');
      }catch (\Throwable $e) {
       DB::rollback();
        return redirect('https://www.festigeek.ch/#!/checkout?state=error');
      }
    }

}
