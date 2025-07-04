<?php

namespace App\Filament\PosApp\Resources\PendapatanResource\Pages;

use App\Filament\PosApp\Resources\PendapatanResource;
use App\Models\Pendapatan;
use App\Models\Penjualan;
use App\Models\PenjualanItem;
use App\Models\Produk;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendapatan extends EditRecord
{
    protected static string $resource = PendapatanResource::class;

    protected array $penjualanItems = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['penjualan_items'] = Pendapatan::with('penjualanItems')->find($data['id'])->penjualanItems;
        if(!empty($data['penjualan_items'])) {
            foreach($data['penjualan_items'] as &$item) {
                $item['harga'] = number_format($item->harga, 0, '.', ',');
                $item['subtotal'] = number_format($item->subtotal, 0, '.', ',');
            }
        }
        $data['total'] = $this->getRecord()->total;
        $data['grand_total'] = $this->getRecord()->grand_total;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total'] = isset($data['total']) ? floatval(\str_replace(['.', ','], '', $data['total'])) : 0;
        $data['grand_total'] = isset($data['grand_total']) ? floatval(\str_replace(['.', ','], '', $data['grand_total'])) : 0;
        $this->penjualanItems = isset($data['penjualan_items']) ? $data['penjualan_items'] : [];

        if(!empty($this->penjualanItems)) {
            foreach($this->penjualanItems as &$item) {
                $item['harga'] = isset($item['harga']) ? floatval(\str_replace(['.', ','], '', $item['harga'])) : 0;
                $item['subtotal'] = isset($item['subtotal']) ? floatval(\str_replace(['.', ','], '', $item['subtotal'])) : 0;
            }
        }

        return $data;
    }

    protected function afterSave():void
    {
        if($this->record->tipe === 'penjualan' && !empty($this->penjualanItems)) {
            $oldPenjualanItems = PenjualanItem::where('pendapatan_id', $this->record->id)->get();
            foreach($oldPenjualanItems as $item) {
                $getProduk = Produk::where('id', $item->produk_id)->first();
                $getProduk->stok += $item->qty;
                $getProduk->save();
            }

            PenjualanItem::where('pendapatan_id', $this->record->id)->delete();

            $this->record->penjualanItems()->createMany($this->penjualanItems);
    
            $getPenjualanItems = PenjualanItem::where('pendapatan_id', $this->record->id)->get();
            foreach($getPenjualanItems as $item) {
                $getProduk = Produk::where('id', $item->produk_id)->first();
                $getProduk->stok -= $item->qty;
                $getProduk->save();
            }
        }
    }
   
}
