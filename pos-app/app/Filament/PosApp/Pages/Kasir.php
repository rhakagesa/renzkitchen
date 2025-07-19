<?php

namespace App\Filament\PosApp\Pages;

use App\Filament\PosApp\Resources\PendapatanResource;
use App\Models\Kategori;
use App\Models\Pendapatan;
use App\Models\Produk;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Kasir extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string $view = 'filament.pos-app.pages.kasir';
    protected static ?string $navigationLabel = 'Kasir';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Kasir';
    public Collection $kategoris;
    public Collection $produks;
    public ?int $selectedKategoriId = null;
    public array $cart = [];
    public int|float $diskon = 0;
    public int|float $pajak = 0;

    public function mount(): void
    {
        $this->kategoris = Kategori::all();
        $this->loadProduk();
    }

    public function loadProduk(): void
    {
        if($this->selectedKategoriId){
            $this->produks = Produk::where('kategori_id', $this->selectedKategoriId)->get();
        } else {
            $this->produks = Produk::select('produks.*')
            ->join('kategoris', 'kategoris.id', '=', 'produks.kategori_id')
            ->where('kategoris.nama', '=', 'Minuman')
            ->get();
        }
    }

    public function selectKategori($id): void
    {
        $this->selectedKategoriId = $id;
        $this->loadProduk();
    }

    public function addToCart($produkId): void
    {
        $produk = Produk::find($produkId);
        if (!$produk) return;

        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['qty'] += 1;
        } else {
            $this->cart[$produkId] = [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'harga' => $produk->harga_jual,
                'qty' => 1,
            ];
        }
    }

    public function removeFromCart($produkId): void
    {
        if (isset($this->cart[$produkId])) {
            unset($this->cart[$produkId]);
        }
    }

    public function incrementQty($produkId): void
    {
        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['qty'] += 1;
        }
    }

    public function decrementQty($produkId): void
    {
        if (isset($this->cart[$produkId]) && $this->cart[$produkId]['qty'] > 1) {
            $this->cart[$produkId]['qty'] -= 1;
        }
    }

    public function getTotal(): float
    {
        $subtotal = collect($this->cart)->sum(fn($item) => $item['harga'] * $item['qty']);
        $diskon = (float) $this->diskon;
        $pajak = (float) $this->pajak;

        $setelahDiskon = max($subtotal - ($subtotal * ($diskon / 100)), 0);
        $total = $setelahDiskon + ($setelahDiskon * ($pajak / 100));

        return $total;
    }

    public function setDiskon(int $diskon): void
    {
        $this->diskon = $diskon;
    }

    public function setPajak(int $pajak): void
    {
        $this->pajak = $pajak;
    }

    public function simpan()
    {
        DB::beginTransaction();

        try {
            $subtotal = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['qty']);
            $diskonRp = ($subtotal * $this->diskon) / 100;
            $setelahDiskon = $subtotal - $diskonRp;
            $pajakRp = ($setelahDiskon * $this->pajak) / 100;
            $grandTotal = $setelahDiskon + $pajakRp;

            $pendapatan = Pendapatan::create([
                'tanggal' => date('Y-m-d'),
                'tipe' => 'penjualan',
                'total' => $subtotal,
                'diskon' => $this->diskon,
                'pajak' => $this->pajak,
                'grand_total' => $grandTotal,
                'keterangan' => 'Penjualan Kasir',
            ]);

            $items = [];
            foreach ($this->cart as $item) {
                $items[] = [
                    'produk_id' => $item['id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['harga'] * $item['qty'],
                ];

                // Update stok produk
                $produk = Produk::find($item['id']);
                if ($produk) {
                    $produk->stok -= $item['qty'];
                    $produk->save();
                }
            }

            $pendapatan->penjualanItems()->createMany($items);

            DB::commit();

            // Reset cart
            $this->cart = [];
            $this->diskon = 0;
            $this->pajak = 0;

            Notification::make()
            ->title('Transaksi berhasil disimpan')
            ->success()
            ->body('Ingin cetak struk?')
            ->actions([
                Action::make('Cetak')
                    ->icon('heroicon-s-printer')
                    ->url(PendapatanResource::getUrl('cetak-nota', ['record' => $pendapatan->id])),
            ])
            ->seconds(10)
            ->send();
            $this->mount();
        } catch (\Exception $e) {
            DB::rollBack();
            // Optional: tampilkan error
            $this->dispatch('notify', title: 'Gagal menyimpan transaksi', body: $e->getMessage(), type: 'danger');
        }
    }
}
