<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function() {
  return ;
});

/*
 * Resource: User
 */
Route::resource('users', 'UserController');
Route::get('users/test', 'UserController@test');
Route::get('users/activate/{token}', 'UserController@activate');
Route::post('users', 'UserController@register');
Route::post('users/login', 'UserController@authenticate');

/*
 * Resource: Qrcode
 */
Route::post('qrcode', 'QrcodeController@create');
Route::post('qrcode/decrypt', 'QrcodeController@decrypt');

/*
 * Resource: Address
 */
Route::resource('users.addresses', 'UserAddressController');

/*
 * ===============================================================================================================
 * ===============================================================================================================
 * TODO: Part to rewrite
 * ===============================================================================================================
 * ===============================================================================================================
 */

/*
 * Resource: Events
 */
//Route::resource('events', 'EventController');

/*
 * Resource: AbstractProducts
 */
//Route::put('abstract_products/{id}', 'AbstractProductController@update');

/*
 * Resource: Inscriptions
 */
//Route::resource('inscriptions', 'InscriptionController');

/*
 * Resource: Orders
 */
/*Route::get('orders/{id}/confirmPaypalPayment', array(
    'as' => 'paypalPayment.status',
    'uses' => 'OrderController@validatePaypalPayment',
));
Route::put('orders/{id}/confirmBankingPayment', 'OrderController@validateBankingPayment');
Route::put('orders/{id}/productConsumption', 'OrderController@consumeProduct');
Route::resource('orders', 'OrderController');*/

/*
* Resource: Products
*/
//Route::resource('products', 'ProductController');