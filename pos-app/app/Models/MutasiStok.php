<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MutasiStok extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'produk_id',
        'bahan_baku',
        'jumlah_produk',
        'keterangan',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
