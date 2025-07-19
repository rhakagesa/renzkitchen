<?php

namespace App\Filament\PosApp\Resources\PendapatanResource\Pages;

use App\Filament\PosApp\Resources\PendapatanResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class CetakNota extends Page
{
    protected static string $resource = PendapatanResource::class;

    protected static string $view = 'filament.pos-app.resources.pendapatan-resource.pages.cetak-nota';

    use InteractsWithRecord;
    
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record)->load('penjualanItems.produk');
    }
}
