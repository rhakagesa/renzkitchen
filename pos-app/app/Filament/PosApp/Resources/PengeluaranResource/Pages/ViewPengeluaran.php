<?php

namespace App\Filament\PosApp\Resources\PengeluaranResource\Pages;

use App\Filament\PosApp\Resources\PengeluaranResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPengeluaran extends ViewRecord
{
    protected static string $resource = PengeluaranResource::class;

    protected function beforeFill(): void
    {
        isset($this->record->bahan_baku) && $this->record->bahan_baku = json_decode($this->record->bahan_baku, true);
    }

    protected function getActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-s-arrow-left')
        ];   
    }
}
