<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoProdutoAdicional extends Model
{

    public function pedidosProdutos(){

        return $this->belongsTo(PedidosProdutos::class);

    }

    public function produto(){

        return $this->belongsTo(Produto::class, 'id', 'id_adicional');

    }

    use SoftDeletes;

    //protected $fillable = ['status', 'envia_cozinha'];

}
