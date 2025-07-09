<?php

namespace App\Filament\PosApp\Resources\BarangRusakResource\Pages;

use App\Filament\PosApp\Resources\BarangRusakResource;
use App\Models\BahanBaku;
use App\Models\BarangRusak;
use App\Models\Produk;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarangRusak extends EditRecord
{
    protected static string $resource = BarangRusakResource::class;

    protected int $oldJumlah = 0;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['nilai_kerugian'] = isset($data['nilai_kerugian']) ? \number_format($data['nilai_kerugian'], 0, '.', ',') : 0;
        $data['total_kerugian'] = isset($data['total_kerugian']) ? \number_format($data['total_kerugian'], 0, '.', ',') : 0;
        $data['barang_rusak_id'] = isset($data['produk_id']) ? $data['produk_id'] : $data['bahan_baku_id'];
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function beforeSave(): void
    {
        $oldBarangRusak = BarangRusak::withoutGlobalScopes()->find($this->record->id);
        $this->oldJumlah = $oldBarangRusak->getOriginal('jumlah');
    }

    protected function afterSave(): void 
    {
        if($this->record->tipe === 'produk') {
            $getProduk = Produk::where('id', $this->record->produk_id)->first();
            $getProduk->stok += ($this->oldJumlah - $this->record->jumlah);
            $getProduk->save();
        } elseif ($this->record->tipe === 'bahan_baku') {
            $getBahanBaku = BahanBaku::where('id', $this->record->bahan_baku_id)->first();
            $getBahanBaku->stok += ($this->oldJumlah - $this->record->jumlah);
            $getBahanBaku->save();
        }
    }
}
