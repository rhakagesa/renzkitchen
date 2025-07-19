<?php

namespace App\Filament\PosApp\Resources\PendapatanResource\Pages;

use App\Filament\PosApp\Resources\PendapatanResource;
use App\Models\Pendapatan;
use App\Models\PenjualanItem;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPendapatan extends ViewRecord
{
    protected static string $resource = PendapatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
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
        $data['total'] = isset($data['total']) ? number_format($data['total'], 0, '.', ',') : 0;
        $data['grand_total'] = isset($data['grand_total']) ? number_format($data['grand_total'], 0, '.', ',') : 0;
        return $data;
    }
}
