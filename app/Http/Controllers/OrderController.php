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
        $order = Order::create($request->all());

        $payer = PayPal::Payer();
        $payer->setPaymentMethod('paypal');

        $products = $request->get('products');

        $itemList = PayPal::ItemList();
        $total = 0;

        foreach ($products as $product){
            $ProductDetails = Product::find($product['product_id']);

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
        $redirectUrls->setReturnUrl(action('OrderController@paypalDone', ['order' => $order->id]));
	    $redirectUrls->setCancelUrl(action('OrderController@paypalCancel', ['order' => $order->id]));


        $payment = PayPal::Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));

        $response = $payment->create($this->_apiContext);
        $redirectUrl = $response->links[1]->href;

        return response()->json($redirectUrl);
    }

    public function paypalDone(Request $request)
    {
        $id = $request->get('paymentId');
        $payer_id = $request->get('PayerID');

        $paymentExecution = PayPal::PaymentExecution();
        $paymentExecution->setPayerId($payer_id);

        $payment = PayPal::getById($id, $this->_apiContext);
        $executePayment = $payment->execute($paymentExecution, $this->_apiContext);

        $order = Order::find($request->get('order'));
        $order->state = 1;
        $order->paypal_paymentId = $id;

        $order->save();

        return response()->json(['success' => 'payment success'], 200);
    }

    public function paypalCancel(Request $request)
    {
//        Order::destroy($request->get('order'));
        $order = Order::find($request->get('order'));
        $order->products()->detach();
        $order->delete();

        return response()->json(['error' => 'payment cancel'], 200);
    }

}
