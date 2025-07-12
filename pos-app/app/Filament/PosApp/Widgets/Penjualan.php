<?php

namespace App\Filament\PosApp\Widgets;

use App\Models\Pendapatan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Penjualan extends BaseWidget
{

    public function getTotalPenjualan()
    {
        return Pendapatan::where('tipe', 'penjualan')->sum('grand_total');
    }
    protected function getStats(): array
    {
        return [
            //
            Stat::make('Penjualan', 'Rp '.\number_format($this->getTotalPenjualan(), 2, ',', '.'))
            ->description('Total Penjualan')
            ->color('success'),
        ];
    }
}
