<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class stock extends Model
{
    //
    public function funcionario(){
        return $this->belongsTo(funcionario::class,'id_funcionario');
    }
   
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto');
    }
    
    public function getAlertaCompletaAttribute()
    {
        $validade = $this->status_validade;

        $stockBaixo = $this->qtd_stock <= 10; // regra ERP

        if ($validade == 'expirado' && $stockBaixo) {
            return 'critico_total';
        }

        if ($validade == 'expirado') {
            return 'expirado';
        }

        if ($stockBaixo) {
            return 'baixo_stock';
        }

        if ($validade == 'critico') {
            return 'critico_validade';
        }

        if ($validade == 'atencao') {
            return 'atencao_validade';
        }

        return 'normal';
    }
  
}