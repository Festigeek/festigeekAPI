<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PayPal;
use App\Order;
use App\Product;
//use Redirect;

class OrderController extends Controller
{

    public function getCheckout(Request $request)
    {

        //$order = new Order();
        //$order->user_id = $request->get(user_id);

        $order = Order::create($request->all());

        $payer = PayPal::Payer();
        $payer->setPaymentMethod('paypal');

        $products = $request->get('products');

        $itemList = PayPal::ItemList();
        $total = 0;

        foreach ($products as $product){
            $ProductDetails = Product::find($product['product_id']);

            $order->products()->save($ProductDetails, ['amount' => $product['amount']]);

            //App\User::find(1)->roles()->save($role, ['expires' => $expires]);

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
        $redirectUrls->setReturnUrl(action('OrderController@getDone'));
	    $redirectUrls->setCancelUrl(action('OrderController@getCancel'));


        $payment = PayPal::Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));

        $response = $payment->create($this->_apiContext);
        $redirectUrl = $response->links[1]->href;

        return response()->json($redirectUrl);
    }

    public function getDone(Request $request)
    {
        $id = $request->get('paymentId');
        $token = $request->get('token');
        $payer_id = $request->get('PayerID');

        $payment = PayPal::getById($id, $this->_apiContext);

        $paymentExecution = PayPal::PaymentExecution();

        $paymentExecution->setPayerId($payer_id);
        $executePayment = $payment->execute($paymentExecution, $this->_apiContext);

        // Clear the shopping cart, write to database, send notifications, etc.

        // Thank the user for the purchase
        return response()->json($id);
//        return view('checkout.done');
    }

    public function getCancel(Request $request)
    {
        // Curse and humiliate the user for cancelling this most sacred payment (yours)
        //return view('checkout.cancel');
    }

}
