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

    public function getBahanBakuAttribute($value)
    {
        $value = isset($value) && $value ? \json_decode($value, true) : null;
        return $value;
    }

    public function setBahanBakuAttribute($value)
    {
        $tempBahanBaku = $value;
        foreach ($tempBahanBaku as &$item) {
            unset($item['item_bahan_baku']);
            unset($item['original_qty']);
        }
        $this->attributes['bahan_baku'] = json_encode($tempBahanBaku);
    }

    protected static function booted()
    {
        static::deleted(function ($mutasiStok) {
            $bahanBakuList = $mutasiStok->bahan_baku;
            $getProduk = Produk::find($mutasiStok->produk_id);
            $jumlahProduksi = $mutasiStok->jumlah_produk;
            foreach ($bahanBakuList as $item) {
                $bahanBaku = BahanBaku::find($item['bahan_baku_id']);
                if ($bahanBaku) {
                    $bahanBaku->stok += (int) $item['qty'];
                    $bahanBaku->save();
                }
            }
            $getProduk->stok -= $jumlahProduksi;
            $getProduk->save();
        });

        static::restored(function ($mutasiStok) {
            $bahanBakuList = $mutasiStok->bahan_baku;
            $getProduk = Produk::find($mutasiStok->produk_id);
            $jumlahProduksi = $mutasiStok->jumlah_produk;
            foreach ($bahanBakuList as $item) {
                $bahanBaku = BahanBaku::find($item['bahan_baku_id']);
                if ($bahanBaku) {
                    $bahanBaku->stok -= (int) $item['qty'];
                    $bahanBaku->save();
                }
            }
            $getProduk->stok += $jumlahProduksi;
            $getProduk->save();
        });
    }
}
