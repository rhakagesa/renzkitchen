<?php

namespace App\Filament\PosApp\Resources\PengeluaranResource\Pages;

use App\Filament\PosApp\Resources\PengeluaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\BahanBaku;
use App\Models\Pengeluaran;
use App\Models\PengeluaranDetail;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if($this->record->tipe === 'beli_bahan_baku') {
            // Load details from pengeluaran_details table
            $data['bahan_baku'] = $this->record->pengeluaranDetails->map(function($detail) {
                return [
                    'id' => $detail->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'jumlah' => $detail->jumlah,
                    'satuan' => $detail->satuan,
                    'harga_satuan' => number_format($detail->harga_satuan, 0, '.', ','), // Format for display
                    'total_harga' => number_format($detail->total_harga, 0, '.', ',')    // Format for display
                ];
            })->toArray();
        }
        
        $data['jumlah_total'] = isset($data['jumlah_total']) ? number_format($data['jumlah_total'], 0, '.', ',') : 0;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['jumlah_total'] = isset($data['jumlah_total']) ? 
            $this->cleanNumber($data['jumlah_total']) : 0;

        // Store old details for stock adjustment
        $this->oldBahanBaku = $this->record->pengeluaranDetails->map(function($detail) {
            return [
                'id' => $detail->id,
                'bahan_baku_id' => $detail->bahan_baku_id,
                'jumlah' => $detail->jumlah,
                'satuan' => $detail->satuan,
                'harga_satuan' => $detail->harga_satuan,
                'total_harga' => $detail->total_harga
            ];
        })->toArray();

        // Clean numbers for new details before saving
        if(isset($data['bahan_baku'])) {
            foreach($data['bahan_baku'] as &$item) {
                $item['harga_satuan'] = isset($item['harga_satuan']) ? 
                    $this->cleanNumber($item['harga_satuan']) : 0;
                $item['total_harga'] = isset($item['total_harga']) ? 
                    $this->cleanNumber($item['total_harga']) : 0;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->tipe === 'beli_bahan_baku') {
            $newBahanBaku = $this->data['bahan_baku'] ?? [];
            
            // Sync pengeluaran details
            $this->syncPengeluaranDetails($newBahanBaku);
            
            // Adjust stock
            $this->adjustStock($newBahanBaku);
        }
    }

    protected function syncPengeluaranDetails(array $newDetails): void
    {
        $currentDetails = $this->record->pengeluaranDetails;
        $existingIds = [];
        
        foreach ($newDetails as $newItem) {
            $detailData = [
                'bahan_baku_id' => $newItem['bahan_baku_id'],
                'jumlah' => $newItem['jumlah'],
                'satuan' => $newItem['satuan'],
                'harga_satuan' => $this->cleanNumber($newItem['harga_satuan']), // Ensure clean number
                'total_harga' => $this->cleanNumber($newItem['total_harga'])    // Ensure clean number
            ];
            
            if (isset($newItem['id'])) {
                // Update existing detail
                PengeluaranDetail::where('id', $newItem['id'])
                    ->update($detailData);
                $existingIds[] = $newItem['id'];
            } else {
                // Create new detail
                $detail = new PengeluaranDetail($detailData);
                $detail->pengeluaran_id = $this->record->id;
                $detail->save();
                $existingIds[] = $detail->id;
            }
        }
        
        // Delete removed details
        PengeluaranDetail::where('pengeluaran_id', $this->record->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    protected function adjustStock(array $newDetails): void
    {
        $oldMap = collect($this->oldBahanBaku)->keyBy('bahan_baku_id');
        $newMap = collect($newDetails)->keyBy('bahan_baku_id');
        
        // Process all affected bahan baku
        $affectedIds = array_unique(array_merge(
            array_column($this->oldBahanBaku, 'bahan_baku_id'),
            array_column($newDetails, 'bahan_baku_id')
        ));
        
        foreach ($affectedIds as $bahanBakuId) {
            $oldItem = $oldMap->get($bahanBakuId);
            $newItem = $newMap->get($bahanBakuId);
            
            $oldTotal = $oldItem ? ($oldItem['jumlah'] * $oldItem['satuan']) : 0;
            $newTotal = $newItem ? ($newItem['jumlah'] * $newItem['satuan']) : 0;
            $difference = $newTotal - $oldTotal;
            
            if ($difference != 0) {
                BahanBaku::where('id', $bahanBakuId)
                    ->increment('stok', $difference);
            }
        }
    }

    /**
     * Helper function to clean formatted numbers for database storage
     */
    protected function cleanNumber($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        return (float) str_replace(['.', ','], '', $value);
    }
}