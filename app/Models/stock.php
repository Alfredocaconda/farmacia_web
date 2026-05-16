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
    
    // Acessor para status de validade
    public function getStatusValidadeAttribute()
    {
        // Se não houver data de caducidade, consideramos como "sem validade"
        if (!$this->caducidade) return 'sem_validade';
        // Calcula a diferença em dias entre a data atual e a data de caducidade
        $dias = Carbon::now()->diffInDays($this->caducidade, false);
        // Define o status com base na quantidade de dias restantes
        if ($dias <= 0) return 'expirado';
        if ($dias <= 5) return 'critico';
        if ($dias <= 10) return 'atencao';
        // Se estiver a mais de 10 dias da caducidade, consideramos como "normal"
        return 'normal';
    }
  
}