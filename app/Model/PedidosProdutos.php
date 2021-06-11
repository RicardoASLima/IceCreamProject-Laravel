<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidosProdutos extends Model
{

    public function pedidos(){

        return $this->belongsTo(Pedidos::class, 'id', 'id_pedidos');

    }

    public function pedidosProdutoAdicional(){

        return $this->hasMany(PedidoProdutoAdicional::class);

    }

    public function produto(){

        return $this->belongsTo(Produto::class, 'id', 'id_produto');

    }

    public function store($data, $idPedido){

        $this->fill([
            'id_produto' => $data['id_produto'],
            'id_pedidos' => $idPedido,
            'quantidade' => $data['quantidade'],
            'valor_total' => $data['valor_total']
        ]);

        $this->save();

    }

    use SoftDeletes;

    //protected $fillable = ['status', 'envia_cozinha'];

}
