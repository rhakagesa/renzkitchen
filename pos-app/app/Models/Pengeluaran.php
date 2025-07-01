<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengeluaran extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'tanggal',
        'tipe',
        'bahan_baku',
        'jumlah_total',
        'keterangan',
    ];
    
    protected static function booted()
    {
        static::deleted(function ($pengeluaran) {
            if ($pengeluaran->tipe === 'beli_bahan_baku') {
                $bahanBakuList = json_decode($pengeluaran->bahan_baku, true);

                foreach ($bahanBakuList as $item) {
                    $bahanBaku = BahanBaku::find($item['bahan_baku_id']);
                    if ($bahanBaku) {
                        $total = (int) $item['qty'] * (int) $item['satuan'];
                        $bahanBaku->stok -= $total;
                        $bahanBaku->save();
                    }
                }
            }
        });

        static::restored(function ($pengeluaran) {
            if ($pengeluaran->tipe === 'beli_bahan_baku') {
                $bahanBakuList = json_decode($pengeluaran->bahan_baku, true);

                foreach ($bahanBakuList as $item) {
                    $bahanBaku = BahanBaku::find($item['bahan_baku_id']);
                    if ($bahanBaku) {
                        $total = (int) $item['qty'] * (int) $item['satuan'];
                        $bahanBaku->stok += $total;
                        $bahanBaku->save();
                    }
                }
            }
        });
    }
}
