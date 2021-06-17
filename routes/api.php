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

Route::get('users', 'LoginController@index'); //login -> funfa

Route::get('produtos',  'ProductController@produtos'); //produtos + sabores -> funfa
Route::get('produtos/adicionais',  'ProductController@produtosAdicionais'); //adicionais -> funfa

Route::get('pedidos/{pedido}',  'OrderController@getProdutoPedidos'); //lista de produtos pedidos
Route::post('pedidos/{pedido}/add',  'OrderController@addPedidos'); //adicionar o pedido

Route::get('comanda',  'AllGetController@getComanda'); //lista de produtos pedidos
Route::get('listapedidos',  'AllGetController@getListaPedidos'); //adicionar o pedido

//Route::get('pedido/{pedido}/produtos',  'OrderController@getProducts');

