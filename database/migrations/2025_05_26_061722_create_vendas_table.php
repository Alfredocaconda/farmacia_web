<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->integer('quantidade'); // qtd vendida
            $table->decimal('preco_unitario', 10, 2); // preço por unidade
            $table->decimal('subtotal', 10, 2); // preco_unitario * quantidade
            $table->date('data_venda'); // data da venda
            $table->string('codigo_fatura');
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->onDelete('cascade');
            $table->string('cliente');
            $table->string('forma_pagamento');
            $table->foreignId('funcionario_id')->constrained('funcionarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};
