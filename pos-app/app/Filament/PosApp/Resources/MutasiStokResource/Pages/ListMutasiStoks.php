<?php

namespace App\Filament\PosApp\Resources\MutasiStokResource\Pages;

use App\Filament\PosApp\Resources\MutasiStokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMutasiStoks extends ListRecords
{
    protected static string $resource = MutasiStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
