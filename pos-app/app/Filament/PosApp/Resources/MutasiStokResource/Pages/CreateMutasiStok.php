<?php

namespace App\Filament\PosApp\Resources\MutasiStokResource\Pages;

use App\Filament\PosApp\Resources\MutasiStokResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMutasiStok extends CreateRecord
{
    protected static string $resource = MutasiStokResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $getProdukItem = Produk::find($this->record->produk_id);
        $getBahanBakuItems = $this->record->bahan_baku;
        
        if($getBahanBakuItems && $getProdukItem){
            foreach($getBahanBakuItems as $bahanBakuItem){
                $bahanBaku = BahanBaku::find($bahanBakuItem['bahan_baku_id']);
                $bahanBaku->update([
                    'stok' => $bahanBaku->stok - $bahanBakuItem['qty']
                ]);
            }

            $getProdukItem->update([
                'stok' => $getProdukItem->stok + $this->record->jumlah_produk
            ]);
        }
    }
}
