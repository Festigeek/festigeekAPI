<?php

/* List of patterns */
Route::pattern('id', '\d+');
Route::pattern('user_id', '\d+|me');

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
Route::get('users/refreshToken', 'UserController@refresh');

Route::get('users/{user_id}/orders', 'UserController@getOrders');
Route::resource('users', 'UserController');
Route::post('users', 'UserController@register');

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
Route::get('events/{event_id}/teams/{team_id}', 'EventController@team');
Route::get('events/{id}/products', 'EventController@products');

Route::get('orders', 'OrderController@index');
Route::post('orders', 'OrderController@create'); //creates a new order, based on type (paypal or banking)
Route::get('orders/done', 'OrderController@paypalDone');
Route::get('orders/cancel', 'OrderController@paypalCancel');
Route::get('orders/{id}', 'OrderController@show');
Route::patch('orders/{id}', 'OrderController@patch');
Route::patch('orders/{order_id}/products/{product_id}', 'OrderController@consumeProduct'); //TODO Create nested routes / controller
Route::delete('orders/{id}', 'OrderController@delete');
Route::get('orders/{order_id}/team', 'OrderController@getTeamDetailed'); //TODO is this ok ?


//Route::resource('orders', 'OrderController'); TODO later, for now, manually created routes
Route::delete('orders/{id}', 'OrderController@destroy');
