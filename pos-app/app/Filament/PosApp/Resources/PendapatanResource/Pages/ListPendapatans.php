<?php

namespace App\Filament\PosApp\Resources\PendapatanResource\Pages;

use App\Filament\PosApp\Resources\PendapatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendapatans extends ListRecords
{
    protected static string $resource = PendapatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
