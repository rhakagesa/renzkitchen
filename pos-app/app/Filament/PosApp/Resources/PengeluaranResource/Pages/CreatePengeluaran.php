<?php

namespace App\Filament\PosApp\Resources\PengeluaranResource\Pages;

use App\Filament\PosApp\Resources\PengeluaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\BahanBaku;
use App\Models\PengeluaranDetail;

class CreatePengeluaran extends CreateRecord
{
    protected static string $resource = PengeluaranResource::class;

    protected array $bahanBakuItems = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['jumlah_total'] = isset($data['jumlah_total']) ? floatval(\str_replace(['.', ','], '', $data['jumlah_total'])) : 0;
        $this->bahanBakuItems = isset($data['bahan_baku']) ? $data['bahan_baku'] : [];

        if(!empty($this->bahanBakuItems)) {
            foreach($this->bahanBakuItems as &$item) {
                $item['harga_satuan'] = isset($item['harga_satuan']) ? floatval(\str_replace(['.', ','], '', $item['harga_satuan'])) : 0;
                $item['total_harga'] = isset($item['total_harga']) ? floatval(\str_replace(['.', ','], '', $item['total_harga'])) : 0;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->tipe === 'beli_bahan_baku' && !empty($this->bahanBakuItems)) {
            $this->record->pengeluaranDetails()->createMany($this->bahanBakuItems);
            
            $getPengeluaranDetails = PengeluaranDetail::where('pengeluaran_id', $this->record->id)->get();
            if (!empty($getPengeluaranDetails)) {
                foreach ($getPengeluaranDetails as $value) {
                    $getBahanBaku = BahanBaku::where('id', $value->bahan_baku_id)->first();
                    $convertSatuan = $value->jumlah * $value->satuan;
                    $getBahanBaku->stok += $convertSatuan;
                    $getBahanBaku->save();
                }
            }
        }
    }

}
