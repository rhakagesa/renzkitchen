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
}
