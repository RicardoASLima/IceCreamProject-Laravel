<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{

    public function pedidosProdutos(){

        return $this->hasMany(PedidosProdutos::class);

    }

    public function pedidoProdutoAdicional(){

        return $this->hasMany(PedidoProdutoAdicional::class);

    }

    use SoftDeletes;

    //protected $fillable = ['status', 'envia_cozinha'];

}
