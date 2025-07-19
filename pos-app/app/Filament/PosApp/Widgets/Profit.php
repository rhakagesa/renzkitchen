<?php

namespace App\Filament\PosApp\Widgets;

use App\Models\BarangRusak;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Filament\Forms\Components\Builder;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Profit extends BaseWidget
{
    use InteractsWithPageFilters;

    public function getStats(): array
    {
        $getPendapatan = Pendapatan::all()->sum('grand_total');
        $getPengeluaran = Pengeluaran::all()->sum('jumlah_total');
        $getKerugian = BarangRusak::all()->sum('total_kerugian');

        $profit = $getPendapatan - ($getPengeluaran + $getKerugian);

        $tanggalSekarang = Carbon::now()->format('d F Y');

        return [
            Stat::make(
                label: 'Total Pendapatan',
                value: 'Rp ' . \number_format($getPendapatan, 0, '.', ','),
            )
            ->description('Periode ' . $tanggalSekarang)
            ->descriptionIcon('heroicon-s-arrow-trending-up')
            ->color('success')
            ,
            Stat::make(
                label: 'Total Pengeluaran',
                value: 'Rp ' . \number_format($getPengeluaran, 0, '.', ','),
            )
            ->description('Periode ' . $tanggalSekarang)
            ->descriptionIcon($getPengeluaran > $getPendapatan ? 'heroicon-s-arrow-trending-up' : 'heroicon-s-arrow-trending-down')
            ->color($getPengeluaran > $getPendapatan ? 'danger' : 'success')
            ,
            Stat::make(
                label: 'Total Kerugian',
                value: 'Rp ' . \number_format($getKerugian, 0, '.', ','),
            )
            ->description('Periode ' . $tanggalSekarang)
            ->descriptionIcon($getKerugian > $getPendapatan ? 'heroicon-s-arrow-trending-up' : 'heroicon-s-arrow-trending-down')
            ->color($getKerugian > $getPendapatan ? 'danger' : 'success')
            ,
            Stat::make(
                label: 'Keuntungan',
                value: 'Rp ' . \number_format($profit, 0, '.', ','),
            )
            ->description('Periode ' . $tanggalSekarang)
            ->descriptionIcon($profit < 0 ? 'heroicon-s-arrow-trending-up' : 'heroicon-s-arrow-trending-down')
            ->color($profit < 0 ? 'danger' : 'success')
            ,

        ];
    }
}
