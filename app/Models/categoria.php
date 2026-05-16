<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class categoria extends Model
{
    //
     public function funcionario(){
        return $this->belongsTo(funcionario::class,'funcionario_id');
    }
}
