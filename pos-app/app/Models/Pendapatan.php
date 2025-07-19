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

    protected static function booted()
    {
        static::softDeleted(function ($pendapatan) {
            $penjualanItems = PenjualanItem::where('pendapatan_id', $pendapatan->id)->get();
            foreach($penjualanItems as $item) {
                $getProduk = Produk::where('id', $item->produk_id)->first();
                $getProduk->stok += $item->qty;
                $getProduk->save();
            }
        });

        static::restored(function ($pendapatan) {
            $penjualanItems = PenjualanItem::where('pendapatan_id', $pendapatan->id)->get();
            foreach($penjualanItems as $item) {
                $getProduk = Produk::where('id', $item->produk_id)->first();
                $getProduk->stok -= $item->qty;
                $getProduk->save();
            }
        });
    }
}
