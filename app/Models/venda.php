<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class venda extends Model
{
    //
     protected $table = 'vendas';

    protected $fillable = [
        'codigo_fatura',
        'stock_id',
        'quantidade',
        'preco_venda',
        'subtotal',
        'cliente',
        'forma_pagamento',
        'data_venda',
        'funcionario_id'
    ];
    public function produto(){
        return $this->belongsTo(Produto::class);
    }
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }
}
