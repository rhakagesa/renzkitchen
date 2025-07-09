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
        'jumlah_total',
        'keterangan',
    ];

    public function pengeluaranDetails(){
        return $this->hasMany(PengeluaranDetail::class);
    }

    protected static function booted()
    {
        static::softDeleted(function ($pengeluaran) {
            if ($pengeluaran->tipe === 'beli_bahan_baku') {
                // Load details from pengeluaran_details relationship
                $details = PengeluaranDetail::where('pengeluaran_id', $pengeluaran->id)->get();
                
                foreach ($details as $detail) {
                    $getBahanBaku = BahanBaku::find($detail->bahan_baku_id);
                    $total = $detail->jumlah * $detail->satuan;
                    $getBahanBaku->stok -= $total;
                    $getBahanBaku->save();
                }
            }
        });
    
        static::restored(function ($pengeluaran) {
            if ($pengeluaran->tipe === 'beli_bahan_baku') {
                // Load details from pengeluaran_details relationship
                $details = PengeluaranDetail::where('pengeluaran_id', $pengeluaran->id)->get();
                
                foreach ($details as $detail) {
                    $getBahanBaku = BahanBaku::find($detail->bahan_baku_id);
                    $total = $detail->jumlah * $detail->satuan;
                    $getBahanBaku->stok += $total;
                    $getBahanBaku->save();
                }
            }
        });
    }
}
