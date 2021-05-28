<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidosProdutos extends Model
{

    public function pedidos(){

        return $this->belongsTo(Pedidos::class);

    }

    public function pedidosProdutoAdicional(){

        return $this->hasMany(PedidoProdutoAdicional::class);

    }

    public function produto(){

        return $this->belongsTo(Produto::class);

    }

    use SoftDeletes;

    //protected $fillable = ['status', 'envia_cozinha'];

}
