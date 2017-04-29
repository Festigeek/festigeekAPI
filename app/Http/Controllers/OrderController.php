<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Mockery\Exception;
use PayPal;
use Crypt;
use App\Order;
use App\Product;

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

        //check if user already regestired a payment order with event_id to his name
        $existingPayment = $currentUser->orders()->where('event_id', $data['event_id'])->get();
        //check dispos tournois
        if($existingPayment){
          return response()->json(['error'=>'You have already created an order for this event']);
        }

        $order = Order::create($data);

        //test if paypal Here
        if($request->get('payment_type_id') == 1){
          $products = $request->get('items');

          $this->createPaypalPayment($order, $products);
        } else if ($request->get('payment_type_id') == 0){
//TODO mail with banking info
        }


    }

    private function createPaypalPayment(Order $order, Array $products){
      $itemList = PayPal::ItemList();
      $total = 0;


              $payer = PayPal::Payer();
              $payer->setPaymentMethod('paypal');



      foreach ($products as $product){
          $ProductDetails = Product::find($product['product_id']);

          //can't test this
          //here test if the items are available
          if(!$ProductDetails->quantity_max<$ProductDetails->sold){
                  return Response::json(['error'=>'No tickets available for ' + $product->name]);
          }

          $order->products()->save($ProductDetails, ['amount' => $product['amount']]);

          $item = PayPal::Item();
          $item->setName($ProductDetails->name)
              ->setCurrency('CHF')
              ->setQuantity($product['amount'])
              ->setPrice($ProductDetails->price);
          $itemList->addItem($item);
          $total += $ProductDetails->price * $product['amount'];
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
          return response()->json($redirectUrl);
      } catch (Exception $ex) {
          $order->products()->detach();
          $order->delete();
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
