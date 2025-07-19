<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengeluaranDetail extends Model
{
    //
    use SoftDeletes;

    protected $table = 'pengeluaran_details';

    protected $fillable = [
        'pengeluaran_id',
        'bahan_baku_id',
        'jumlah',
        'satuan',
        'harga_satuan',
        'total_harga',
    ];

    public function pengeluaran(){
        return $this->belongsTo(Pengeluaran::class);
    }
}
