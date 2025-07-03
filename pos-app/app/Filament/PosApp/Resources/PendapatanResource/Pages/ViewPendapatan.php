<?php

namespace App\Filament\PosApp\Resources\PendapatanResource\Pages;

use App\Filament\PosApp\Resources\PendapatanResource;
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
}
