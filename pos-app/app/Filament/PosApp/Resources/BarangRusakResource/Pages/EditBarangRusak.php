<?php

namespace App\Filament\PosApp\Resources\BarangRusakResource\Pages;

use App\Filament\PosApp\Resources\BarangRusakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarangRusak extends EditRecord
{
    protected static string $resource = BarangRusakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
