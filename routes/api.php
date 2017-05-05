<?php

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
  return response()->json(['success' => 'Festigeek API v1']);
});

/*
 * Resource: User
 */
Route::get('users/test', 'UserController@test');
Route::get('users/activate', 'UserController@activation');
Route::post('users/login', 'UserController@authenticate');
Route::resource('users', 'UserController');
Route::post('users', 'UserController@register');

Route::get('users/{id}/orders', 'UserOrderController@getOrders');

/*
 * Resource: Qrcode
 */
Route::post('qrcode', 'QrcodeController@create');
Route::post('qrcode/decrypt', 'QrcodeController@decrypt');

/*
 * Resource: Country
 */
Route::resource('countries', 'CountryController');

/*
 * Resource: Address
 */
//Route::resource('users.addresses', 'UserAddressController');

/*
 * Resource: Products
 */
Route::resource('productTypes', 'ProductTypesController', ['only' => [
    'index'
]]);
Route::resource('products', 'ProductController');

Route::get('events/current', 'EventController@current');
Route::get('events/{id}/teams', 'EventController@teams');
Route::get('events/{id}/products', 'EventController@products');

Route::post('orders', 'OrderController@create'); //creates a new order, based on type (paypal or banking)
Route::get('orders/done', 'OrderController@paypalDone');
Route::get('orders/cancel', 'OrderController@paypalCancel');

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
