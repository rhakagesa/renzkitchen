<?php

namespace App\Filament\PosApp\Resources\BarangRusakResource\Pages;

use App\Filament\PosApp\Resources\BarangRusakResource;
use App\Models\BahanBaku;
use App\Models\Produk;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBarangRusak extends CreateRecord
{
    protected static string $resource = BarangRusakResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nilai_kerugian'] = isset($data['nilai_kerugian']) ? floatval(\str_replace(['.', ','], '', $data['nilai_kerugian'])) : 0;
        $data['total_kerugian'] = isset($data['total_kerugian']) ? floatval(\str_replace(['.', ','], '', $data['total_kerugian'])) : 0;
        if($data['tipe'] === 'produk' && !empty($data['barang_rusak_id'])) {
            $data['produk_id'] = $data['barang_rusak_id']; 
        } elseif ($data['tipe'] === 'bahan_baku' && !empty($data['barang_rusak_id'])) {
            $data['bahan_baku_id'] = $data['barang_rusak_id'];
        }
        unset($data['stok']);
        unset($data['satuan']);
        unset($data['barang_rusak_id']);
        return $data;
    }
    
    protected function afterCreate():void
    {
        if($this->record->tipe === 'produk' && !empty($this->record->produk_id)) {
            $getProduk = Produk::where('id', $this->record->produk_id)->first();
            $getProduk->stok -= $this->record->jumlah;
            $getProduk->save();
        } elseif ($this->record->tipe === 'bahan_baku' && !empty($this->record->bahan_baku_id)) {
            $getBahanBaku = BahanBaku::where('id', $this->record->bahan_baku_id)->first();
            $getBahanBaku->stok -= $this->record->jumlah;
            $getBahanBaku->save();
        }
    }
}       
