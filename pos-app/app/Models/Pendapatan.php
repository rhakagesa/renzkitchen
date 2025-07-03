<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pendapatan extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'tanggal',
        'tipe',
        'total',
        'diskon',
        'pajak',
        'grand_total',
        'keterangan'
    ];

    public function penjualanItems()
    {
        return $this->hasMany(PenjualanItem::class);
    }
}
