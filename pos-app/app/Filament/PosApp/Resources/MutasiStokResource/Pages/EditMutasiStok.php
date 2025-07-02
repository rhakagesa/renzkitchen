<?php

namespace App\Filament\PosApp\Resources\MutasiStokResource\Pages;

use App\Filament\PosApp\Resources\MutasiStokResource;
use App\Models\BahanBaku;
use App\Models\MutasiStok;
use App\Models\Produk;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMutasiStok extends EditRecord
{
    protected static string $resource = MutasiStokResource::class;

    protected array $oldBahanBaku = [];

    protected int $oldJumlahProduksi = 0;

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
        $oldMutasiStok = MutasiStok::withoutGlobalScopes()->find($this->record->id);
        $oldBahanBaku = $oldMutasiStok->getOriginal('bahan_baku');
        $this->oldBahanBaku = $oldBahanBaku;
        $this->oldJumlahProduksi = $oldMutasiStok->getOriginal('jumlah_produk');    
    }

    protected function afterSave(): void
    {

        // Ambil data lama sebelum update
        $oldBahanBaku = $this->oldBahanBaku;
        $newBahanBaku = $this->record->bahan_baku;
    
        // Siapkan mapping data lama untuk perbandingan cepat
        $oldMap = collect($oldBahanBaku)->keyBy('bahan_baku_id');
    
            foreach ($newBahanBaku as $item) {
                $bahanBakuId = $item['bahan_baku_id'];
                $newQty = (int) $item['qty'];
                
                $oldItem = $oldMap->get($bahanBakuId);
                $oldQty = 0;
                if ($oldItem) {
                    $oldQty = (int) $oldItem['qty'];
                    
                    // Hapus dari map agar sisanya nanti bisa dianggap "dihapus"
                    $oldMap->forget($bahanBakuId);
                }
    
                // Update stok: selisih dari baru - lama
                $selisih = $oldQty - $newQty;
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
                    $stokPengurangan = (int) $oldItem['qty'];
                    $bahanBaku->stok -= $stokPengurangan;
                    $bahanBaku->save();
                }
            }

        $getProduk = Produk::find($this->record->produk_id);
        $selisihJumlahProduk = $this->record->jumlah_produk - $this->oldJumlahProduksi;
        $getProduk->stok += $selisihJumlahProduk;
        $getProduk->save();
    }
}

