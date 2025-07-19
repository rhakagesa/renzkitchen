<?php

namespace App\Filament\PosApp\Widgets;

use App\Models\Produk;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StokMenipis extends BaseWidget
{

    protected function getProduks(): array
    {
        return Produk::where('stok', '<=' , 5)->get()->toArray();
    }

    protected function getHeading(): string
    {
        return 'Stok Menipis';
    }

    protected function getStats(): array
    {

        $result = [];

        foreach ($this->getProduks() as $produk) {
            $result[] = Stat::make($produk['nama'], $produk['stok'])
                ->color($produk['stok'] == 0 ? 'danger' : 'warning')
                ->description($produk['stok'] == 0 ? 'Habis' : 'Menipis')
                ->descriptionIcon($produk['stok'] == 0 ? 'heroicon-s-x-circle' : 'heroicon-s-exclamation-circle');
        }

        return $result;
    }
}
