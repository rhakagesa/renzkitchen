<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangRusak extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'tanggal',
        'tipe',
        'produk_id',
        'bahan_baku_id',
        'jumlah',
        'nilai_kerugian',
        'total_kerugian',
        'keterangan'
    ];
    
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function getNamaBarangAttribute(){
        if($this->tipe == 'produk') {
            return $this->produk->nama;
        } else {
            return $this->bahan_baku->nama;
        }
    }

    protected static function booted()
    {
        static::softDeleted(function ($barangRusak) {
            if($barangRusak->tipe == 'produk' && !empty($barangRusak->produk_id)) {
                $getProduk = Produk::where('id', $barangRusak->produk_id)->first();
                $getProduk->stok += $barangRusak->jumlah;
                $getProduk->save();
            } elseif ($barangRusak->tipe == 'bahan_baku' && !empty($barangRusak->bahan_baku_id)) {
                $getBahanBaku = BahanBaku::where('id', $barangRusak->bahan_baku_id)->first();
                $getBahanBaku->stok += $barangRusak->jumlah;
                $getBahanBaku->save();
            }
        });

        static::restored(function ($barangRusak) {
            if($barangRusak->tipe == 'produk' && !empty($barangRusak->produk_id)) {
                $getProduk = Produk::where('id', $barangRusak->produk_id)->first();
                $getProduk->stok -= $barangRusak->jumlah;
                $getProduk->save();
            } elseif ($barangRusak->tipe == 'bahan_baku' && !empty($barangRusak->bahan_baku_id)) {
                $getBahanBaku = BahanBaku::where('id', $barangRusak->bahan_baku_id)->first();
                $getBahanBaku->stok -= $barangRusak->jumlah;
                $getBahanBaku->save();
            }
        });
    }
}
