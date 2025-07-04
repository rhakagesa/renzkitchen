<?php

namespace App\Filament\PosApp\Resources\PengeluaranResource\Pages;

use App\Filament\PosApp\Resources\PengeluaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\BahanBaku;
use App\Models\Pengeluaran;

class EditPengeluaran extends EditRecord
{
    protected static string $resource = PengeluaranResource::class;

    protected array $oldBahanBaku = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        if ($this->record->tipe === 'beli_bahan_baku') {
            $oldPengeluaran = Pengeluaran::withoutGlobalScopes()->find($this->record->id);
            $oldBahanBaku = $oldPengeluaran->getOriginal('bahan_baku');
            $this->oldBahanBaku = $oldBahanBaku;    
        }
    }

    protected function afterSave(): void
    {
        if ($this->record->tipe === 'beli_bahan_baku') {
            // Ambil data lama sebelum update
            $oldBahanBaku = $this->oldBahanBaku;
            $newBahanBaku = $this->record->bahan_baku;
    
            // Siapkan mapping data lama untuk perbandingan cepat
            $oldMap = collect($oldBahanBaku)->keyBy('bahan_baku_id');
    
            foreach ($newBahanBaku as $item) {
                $bahanBakuId = $item['bahan_baku_id'];
                $newQty = (int) $item['qty'];
                $newSatuan = (int) $item['satuan'];
                $newTotal = $newQty * $newSatuan;
    
                $oldItem = $oldMap->get($bahanBakuId);
                $oldTotal = 0;
                if ($oldItem) {
                    $oldQty = (int) $oldItem['qty'];
                    $oldSatuan = (int) $oldItem['satuan'];
                    $oldTotal = $oldQty * $oldSatuan;
                    // Hapus dari map agar sisanya nanti bisa dianggap "dihapus"
                    $oldMap->forget($bahanBakuId);
                }
    
                // Update stok: selisih dari baru - lama
                $selisih = $newTotal - $oldTotal;
                $bahanBaku = BahanBaku::find($bahanBakuId);
                if ($bahanBaku) {
                    $bahanBaku->stok += $selisih;
                    $bahanBaku->save();
                }
            }
    
            // Jika ada sisa di oldMap, artinya data itu dihapus di update
            foreach ($oldMap as $oldItem) {
                $bahanBaku = BahanBaku::find($oldItem['bahan_baku_id']);
                if ($bahanBaku) {
                    $stokPengurangan = (int) $oldItem['qty'] * (int) $oldItem['satuan'];
                    $bahanBaku->stok -= $stokPengurangan;
                    $bahanBaku->save();
                }
            }
        }
    }    
}
