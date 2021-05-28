<?php


namespace App\Http\Controllers;

use App\Model\Produto;

class ProductController
{

    public function produtos(){

        $result = Produto::where('categorias')->get();

        return $result;

    }

}
