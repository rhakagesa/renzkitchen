<?php

namespace App\Filament\PosApp\Resources\MutasiStokResource\Pages;

use App\Filament\PosApp\Resources\MutasiStokResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMutasiStok extends EditRecord
{
    protected static string $resource = MutasiStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
