<?php


namespace App\Http\Controllers;


use App\Model\Pedidos;
use Illuminate\Support\Facades\Request;

class OrderController
{

    public function getProducts(Pedidos $pedido, Request $request) {
        return response(["produtos" => $pedido->pedidosProdutos->load(['produto', 'pedidosProdutoAdicional', 'pedidosProdutoAdicional.produto'])], 200);
    }

}
