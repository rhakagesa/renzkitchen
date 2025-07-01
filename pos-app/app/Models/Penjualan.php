<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penjualan extends Model
{
    //
    use SoftDeletes;

    public function penjualanItems()
    {
        return $this->hasMany(PenjualanItem::class);
    }
}
