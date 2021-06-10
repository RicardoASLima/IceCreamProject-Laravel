<?php


namespace App\Http\Controllers;

use App\Model\Pedidos;
use App\Model\PedidosProdutos;
use Illuminate\Support\Facades\Request;

class OrderController
{

    public function getProdutoPedidos(Pedidos $pedido, Request $request) {

        return response($pedido->pedidosProdutos->load(['produto', 'pedidosProdutoAdicional', 'pedidosProdutoAdicional.produto']), 200);

    }

    public function addPedidos(Pedidos $pedido, Request $request){

        $result = PedidosProdutos::store($request->all(), $pedido->id);

        return $result;

    }

}
