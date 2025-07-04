<?php

namespace App\Filament\PosApp\Resources\BarangRusakResource\Pages;

use App\Filament\PosApp\Resources\BarangRusakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarangRusaks extends ListRecords
{
    protected static string $resource = BarangRusakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
