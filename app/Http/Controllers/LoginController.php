<?php

namespace App\Http\Controllers;

use App\Model\Mesa;
use App\Model\Pedidos;
use http\Env\Response;
use Illuminate\Http\Request;

class LoginController {

    public function index (Request $request){

        $request = 1;

        $result = Mesa::where('id', $request)->first();

        $result->status = 'ativo';
        $result->save();

        $pedido = new Pedidos();
        $pedido->id_mesa = $result->id;
        $pedido->status = 'ativo';

        $pedido->save();

        return response($pedido->load(['mesa']), 200);
    }



}
