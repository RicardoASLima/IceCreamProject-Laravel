<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

header('Access-Control-Allow-Origin', "*");
header('Access-Control-Allow-Methods', "GET, POST, PATCH, PUT, DELETE, OPTIONS");
header('Access-Control-Allow-Headers', "Origin, Content-Type, X-Auth-Token, DNT");

Route::get('users', 'LoginController@index');
Route::get('produtos',  'ProductController@produtos');

//Route::get('pedido/{pedido}/produtos',  'OrderController@getProducts');

