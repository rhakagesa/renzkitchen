<?php

namespace App\Filament\PosApp\Resources\PendapatanResource\Pages;

use App\Filament\PosApp\Resources\PendapatanResource;
use App\Models\PenjualanItem;
use App\Models\Produk;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePendapatan extends CreateRecord
{
    protected static string $resource = PendapatanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total'] = isset($data['total']) ? floatval(\str_replace(['.', ','], '', $data['total'])) : 0;
        $data['grand_total'] = isset($data['grand_total']) ? floatval(\str_replace(['.', ','], '', $data['grand_total'])) : 0;

        return $data;
    }

    protected function afterCreate():void
    {
        if($this->record->tipe === 'penjualan') {
            $this->record->penjualanItems()->createMany($this->record->penjualan_items);
        }

        $getPenjualanItems = PenjualanItem::where('pendapatan_id', $this->record->id)->get();
        foreach($getPenjualanItems as $item) {
            $getProduk = Produk::where('id', $item->produk_id)->first();
            $getProduk->stok -= $item->qty;
            $getProduk->save();
        }
    }
}
