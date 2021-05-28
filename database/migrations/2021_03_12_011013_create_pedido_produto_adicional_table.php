<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoProdutoAdicionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_produto_adicional', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_adicional')->unsigned();
            $table->foreign('id_adicional')->references('id')->on('produtos');
            $table->integer('id_pedido_produto')->unsigned();
            $table->foreign('id_pedido_produto')->references('id')->on('pedidos_produtos');
            $table->float('valor_total');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedido_produto_adicional');
    }
}
