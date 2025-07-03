<?php

namespace App\Filament\PosApp\Resources\PendapatanResource\Pages;

use App\Filament\PosApp\Resources\PendapatanResource;
use App\Models\Pendapatan;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendapatan extends EditRecord
{
    protected static string $resource = PendapatanResource::class;

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
}
