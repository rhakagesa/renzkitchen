<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanItem extends Model
{
    //
    use SoftDeletes;

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function pendapatan()
    {
        return $this->belongsTo(Pendapatan::class);
    }
}
