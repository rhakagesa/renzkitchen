<?php

namespace App\Filament\PosApp\Resources\BahanBakuResource\Pages;

use App\Filament\PosApp\Resources\BahanBakuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBahanBaku extends EditRecord
{
    protected static string $resource = BahanBakuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
