<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanBaku extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'nama',
        'satuan',
        'stok',
    ];
}
