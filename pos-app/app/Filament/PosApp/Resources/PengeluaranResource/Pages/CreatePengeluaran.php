<?php

namespace App\Filament\PosApp\Resources\PengeluaranResource\Pages;

use App\Filament\PosApp\Resources\PengeluaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\BahanBaku;

class CreatePengeluaran extends CreateRecord
{
    protected static string $resource = PengeluaranResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        if ($this->record->tipe === 'beli_bahan_baku') {
            $bahanBakuItems = $this->record->bahan_baku;
            foreach ($bahanBakuItems as $key => $value) {
                $getBahanBaku = BahanBaku::find($value['bahan_baku_id']);
                
                if ($getBahanBaku) {
                    $convertSatuan = $value['qty'] * $value['satuan'];  
                    $getBahanBaku->stok += $convertSatuan;
                    $getBahanBaku->save();
                }
            }
        }
    }

}
