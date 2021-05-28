<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{

    public function pedidos(){

        return $this->hasMany(Pedidos::class);
    }

    use SoftDeletes;

    //protected $fillable = ['status', 'envia_cozinha'];

}
