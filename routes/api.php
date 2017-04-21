<?php

use Illuminate\Http\Request;

/* List of patterns */
Route::pattern('id', '\d+');

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
 * Resource: Products
 */
Route::resource('productTypes', 'ProductTypesController', ['only' => [
    'index'
]]);
Route::resource('products', 'ProductController');
Route::resource('productTypes/{id}/products', 'ProductController@index');

Route::get('events/{id}/teams/{game?}', 'EventController@teams');

Route::post('orders', 'OrderController@getCheckout');
Route::get('orders/done', 'OrderController@getDone');
Route::get('orders/cancel', 'OrderController@getCancel');

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
