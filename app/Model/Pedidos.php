<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedidos extends Model
{

    use SoftDeletes;

    public function mesa(){

        return $this->belongsTo(Mesa::class, 'id', 'id_mesa');

    }

    public function pedidosProdutos(){

        return $this->hasMany(PedidosProdutos::class);

    }

    //protected $fillable = ['status', 'envia_cozinha'];

}
