<?php


namespace App\Http\Controllers;

use App\Model\Produto;

class ProductController
{

    public function produtos(){

        $result = Produto::where('categorias', '<>', 'adicionais')->get();

        return $result;

    }

    public function produtosAdicionais(){

        $result = Produto::where('categorias', 'adicionais')->get();

        return $result;

    }


}
