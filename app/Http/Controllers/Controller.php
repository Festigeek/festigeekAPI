<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Mail;
use PayPal;
use JWTAuth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $_apiContext;

    public function __construct() {
        if(\Config::get('mail.driver') === 'smtp') {
            // Send email notification
            $transport = \Swift_SmtpTransport::newInstance(
                \Config::get('mail.host'),
                \Config::get('mail.port'),
                \Config::get('mail.encryption'))
                    ->setUsername(\Config::get('mail.username'))
                    ->setPassword(\Config::get('mail.password'))
                    ->setStreamOptions(['ssl' => \Config::get('mail.ssloptions')]);

            $mailer = \Swift_Mailer::newInstance($transport);
            Mail::setSwiftMailer($mailer);
        }
        
        $this->_apiContext = PayPal::ApiContext(
            config('services.paypal.client_id'),
            config('services.paypal.secret'));

        $this->_apiContext->setConfig(array(
            'mode' => 'sandbox',
            'service.EndPoint' => 'https://api.sandbox.paypal.com',
            'http.ConnectionTimeOut' => 60,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'FINE'
        ));
    }

    protected function isAdminOrOwner($user_id) {
        return $user_id === 'me' || JWTAuth::user()->id == $user_id || JWTAuth::user()->hasRole('admin');
    }
}
