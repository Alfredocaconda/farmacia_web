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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('preco_venda', 10, 2);
            $table->integer('qtd_stock');
            $table->string('codigo_barra');
            $table->string('fornecedor');
            $table->date('caducidade');
            $table->date('data_entrada');
            $table->foreignId('id_funcionario')->constrained('funcionarios')->onDelete('cascade');
            $table->foreignId('id_produto')->constrained('produtos')->onDelete('cascade');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
