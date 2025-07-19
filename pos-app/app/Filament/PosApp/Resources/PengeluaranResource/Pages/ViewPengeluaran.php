<?php

namespace App\Filament\PosApp\Resources\PengeluaranResource\Pages;

use App\Filament\PosApp\Resources\PengeluaranResource;
use App\Models\Pengeluaran;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPengeluaran extends ViewRecord
{
    protected static string $resource = PengeluaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if($this->record->tipe === 'beli_bahan_baku') {
            $data['bahan_baku'] = Pengeluaran::with('pengeluaranDetails')->find($data['id'])->pengeluaranDetails;
            foreach($data['bahan_baku'] as $key => &$item) {
                $item['harga_satuan'] = isset($item['harga_satuan']) ? number_format($item['harga_satuan'], 0, '.', ',') : 0;
                $item['total_harga'] = isset($item['total_harga']) ? number_format($item['total_harga'], 0, '.', ',') : 0;
            }
        }
        $data['jumlah_total'] = isset($data['jumlah_total']) ? number_format($data['jumlah_total'], 0, '.', ',') : 0;
        return $data;
    }
}
