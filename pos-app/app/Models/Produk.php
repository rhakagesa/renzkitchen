<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'nama',
        'harga_jual',
        'kategori_id',
        'stok',
        'gambar',
        'keterangan',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function mutasiStok()
    {
        return $this->hasMany(MutasiStok::class);
    }

    public function penjualanItems()
    {
        return $this->hasMany(PenjualanItem::class);
    }
}