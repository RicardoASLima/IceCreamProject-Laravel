<?php

namespace App\Http\Controllers;

use App\Model\Comanda;
use App\Model\ListaPedidos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllGetController {

    public function getComanda (Request $request){

        $result = DB::table('comanda')->get();

        dd($result);

        return $result;

    }

    public function getListaPedidos (Request $request){

        $result = DB::table('lista_pedidos')->get();

        return $result;

    }



}
