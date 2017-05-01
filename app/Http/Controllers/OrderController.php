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

use App\Mail\BankingWireTransfertMail;

use JWTAuth;

class OrderController extends Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->middleware('jwt.auth');
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


    try{
//	    DB::beginTransaction();

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
				  return response()->json(['error' => 'Product not from the same event']);
			  }

			  //we find an subscription
			  if($ProductDetails->product_types_id === 1)
			    ++$nbSubscription;

			  //test if there is not more then 1 subscription
			  if($nbSubscription > 1) {
				  DB::rollback();
				  return response()->json(['error' => 'No more than one inscription']);
			  }

			  //here test if the items are available
			  if ($ProductDetails->quantity_max != null && $ProductDetails->sold >= $ProductDetails->quantity_max) {
				  DB::rollback();
			  	return response()->json(['error' => 'No more tickets available']);
			  }

			  ++$ProductDetails->sold;
			  $ProductDetails->save();
			  $order->products()->save($ProductDetails, ['amount' => $product['amount']]);
			  $total += $ProductDetails->price * $product['amount'];
		  }

		  if($request->has('team')) {
        $team = Team::firstOrNew(array('name' => $request->get('team')));
        $team->save();
        $order->team()->attach($team->id, ['captain' => false, 'user_id' => $currentUser->id]);
      }


//	    DB::commit();
	  }catch (Exception $e){
	    DB::rollback();
			return response()->json(['error'=>'json not valid']);
		}

    //test if paypal or bank transfert Here
    if($request->get('payment_type_id') === 2){
      $this->createPaypalPayment($order);
    } else if ($request->get('payment_type_id') === 1){

          //TODO find better way than a foreach
          foreach ($products as $product){
              $ProductDetails = Product::find($product['product_id']);

              //can't test this
              //here test if the items are available
              if((!$ProductDetails->quantity_max<$ProductDetails->sold) || $ProductDetails->quantity_max == null ){
                      return Response::json(['error'=>'No more tickets available']);
              }

              $order->products()->save($ProductDetails, ['amount' => $product['amount']]);


              $total += $ProductDetails->price * $product['amount'];
            }
              //TODO test when deploy
              Mail::to($newuser->email, $newuser->username)->send(new BankingWireTransfertMail($newuser, $total)); //TODO send amount
        }


    }

    private function createPaypalPayment($order){
//	    $order = Order::findOrFail($orderId);

      $payer = PayPal::Payer();
      $payer->setPaymentMethod('paypal');

	    $itemList = PayPal::ItemList();
	    $total = 0;

	    foreach($order->products()->get() as $product){
		    $item = PayPal::Item();
		    $item->setName($product->name)
			    ->setCurrency('CHF')
			    ->setQuantity($product->pivot->amount)
			    ->setPrice($product->price);
		    $itemList->addItem($item);
		    $total += $product->price * $product->pivot->amount;
	    }

	    //dd($total);

//      foreach ($products as $product){
//          $ProductDetails = Product::find($product['product_id']);
//
//          //can't test this
//          //here test if the items are available
//          if((!$ProductDetails->quantity_max<$ProductDetails->sold) || $ProductDetails->quantity_max == null ){
//                  return Response::json(['error'=>'No more tickets available ']);
//          }
//
//          $order->products()->save($ProductDetails, ['amount' => $product['amount']]);
//
//          $item = PayPal::Item();
//          $item->setName($ProductDetails->name)
//              ->setCurrency('CHF')
//              ->setQuantity($product['amount'])
//              ->setPrice($ProductDetails->price);
//          $itemList->addItem($item);
//          $total += $ProductDetails->price * $product['amount'];
//      }

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
      $redirectUrls->setReturnUrl(action('OrderController@paypalDone', ['order' => $cryptOrderID]));
      $redirectUrls->setCancelUrl(action('OrderController@paypalCancel', ['order' => $cryptOrderID]));


      $payment = PayPal::Payment();
      $payment->setIntent('sale');
      $payment->setPayer($payer);
      $payment->setRedirectUrls($redirectUrls);
      $payment->setTransactions(array($transaction));

      try{
          $response = $payment->create($this->_apiContext);
          $redirectUrl = $response->links[1]->href;

          //dd($redirectUrl);
          return response()->json(['link' =>$redirectUrl], 200);
      } catch (Exception $ex) {
          return response()->json(['error' => 'paypal error'], 200);
          //$order->products()->detach();
          //$order->delete();
      }
    }

    public function paypalDone(Request $request)
    {
        $id = $request->get('paymentId');
        $payer_id = $request->get('PayerID');

        $paymentExecution = PayPal::PaymentExecution();
        $paymentExecution->setPayerId($payer_id);

        $order = Order::find(Crypt::decrypt($request->get('order')));
        try {
            $payment = PayPal::getById($id, $this->_apiContext);
            $executePayment = $payment->execute($paymentExecution, $this->_apiContext);

            $order->state = 1;
            //TODO change the number sold, increment

            $order->paypal_paymentId = $id;

            $order->save();

            return response()->json(['success' => 'payment success'], 200);
        } catch (Exception $ex){
            $order->products()->detach();
            $order->delete();
        }
    }

    public function paypalCancel(Request $request)
    {
        $order = Order::find(Crypt::decrypt($request->get('order')));
        $order->products()->detach();
        $order->delete();

        return response()->json(['error' => 'payment cancel'], 200);
    }

}
