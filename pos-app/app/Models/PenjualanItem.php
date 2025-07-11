<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanItem extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'produk_id',
        'pendapatan_id',
        'penjualan_id', 
        'qty', 
        'harga', 
        'subtotal',
    ];

    public function pendapatan()
    {
        return $this->belongsTo(Pendapatan::class);
    }
}
