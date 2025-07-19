<x-filament::page>
    <div class="flex flex-col lg:flex-row gap-4 w-full">
        {{-- Katalog dan Kategori --}}
        <div class="flex flex-col gap-4 lg:w-3/4 w-full">
            {{-- Sidebar Kategori --}}
            <div class="w-full rounded-xl shadow p-4">
                <h3 class="text-lg font-semibold mb-4">Kategori</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->kategoris as $kategori)
                        <x-filament::button
                            size="sm"
                            wire:click="selectKategori({{ $kategori->id }})"
                            color="{{ $kategori->id === $this->selectedKategoriId ? 'primary' : 'gray' }}"
                        >
                            {{ $kategori->nama }}
                        </x-filament::button>
                    @endforeach
                </div>
            </div>

            {{-- Produk --}}
            <div class="w-full rounded-xl shadow p-4">
                <h3 class="text-lg font-semibold mb-4">Produk</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach ($this->produks as $produk)
                        <button
                            @disabled($produk->stok <= 0)
                            wire:click="addToCart({{ $produk->id }})"
                            class="flex flex-col items-center text-center rounded-lg border p-2 transition"
                        >
                            <img src="{{ asset('storage/' . $produk->gambar) }}"
                                 alt="{{ $produk->nama }}"
                                 class="w-full h-24 object-contain mb-2">
                            <p class="text-sm font-medium line-clamp-1">{{ $produk->nama }}</p>
                            <p class="text-sm text-gray-600">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                            <x-filament::badge
                                color="primary"
                                size="xs"
                            >
                                Stok: {{ $produk->stok }}
                            </x-filament::badge>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Invoice --}}
        <div class="w-full lg:w-[300px] rounded-xl shadow p-4 min-h-[450px] max-h-[90vh] overflow-y-auto">
            <div class="w-full flex flex-col items-center">
                <img src="{{ asset('storage/logo-nota.png') }}" alt="Logo" style="width: auto; height: 80px;">
                <h3 class="text-md font-semibold">Jln. Tirta Tawar No. 1</h3>
                <p class="text-sm font-medium">0811 1111 1111</p>
                <p class="text-xs">{{date('d-m-Y H:i:s')}}</p>
            </div>
            @forelse ($this->cart as $item)
            <div class="flex items-start justify-between mb-4 border-b pb-2">
                {{-- Kiri: Tombol Hapus --}}
                <div class="flex flex-col items-center justify-start pt-1">
                    <x-filament::icon-button
                        wire:click="removeFromCart({{ $item['id'] }})"
                        color="danger"
                        icon="heroicon-s-trash"
                        size="xs"
                        class="mb-1"
                    />
                </div>
            
                {{-- Tengah: Info + Qty Control --}}
                <div class="flex-1 px-2">
                    <p class="font-medium text-sm leading-tight">{{ $item['nama'] }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <x-filament::icon-button
                            wire:click="decrementQty({{ $item['id'] }})"
                            icon="heroicon-s-minus"
                            size="xs"
                            color="gray"
                        />
            
                        <p class="text-sm w-6 text-center">{{ $item['qty'] }}</p>
            
                        <x-filament::icon-button
                            wire:click="incrementQty({{ $item['id'] }})"
                            icon="heroicon-s-plus"
                            size="xs"
                            color="primary"
                        />
                    </div>
                </div>
            
                {{-- Kanan: Total Harga --}}
                <div class="text-right text-sm font-semibold min-w-[80px]">
                    Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}
                </div>
            </div>
            
            @empty
                <p class="text-gray-500">Belum ada item.</p>
            @endforelse
            
            @php
                $subtotal = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['qty']);
                $diskonRp = ($subtotal * $this->diskon) / 100;
                $setelahDiskon = $subtotal - $diskonRp;
                $pajakRp = ($setelahDiskon * $this->pajak) / 100;
            @endphp

            <div class="text-sm text-gray-600 mb-1">Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
            <div class="text-sm text-gray-600 mb-1">Diskon: Rp {{ number_format($diskonRp, 0, ',', '.') }} ({{ $this->diskon }}%)</div>
            <div class="text-sm text-gray-600 mb-1">Pajak: Rp {{ number_format($pajakRp, 0, ',', '.') }} ({{ $this->pajak }}%)</div>

            <div class="mt-4">
                <label class="text-sm font-medium">Diskon (%)</label>
                <x-filament::input
                    type="number"
                    wire:model.live="diskon"
                    min="0"
                    max="100"
                    oninput="this.value = this.value > 100 ? 100 : Math.max(0, this.value)"
                    class="w-full text-sm"
                />
            </div>
            
            <div class="mt-2">
                <label class="text-sm font-medium">Pajak (%)</label>
                <x-filament::input
                    type="number"
                    wire:model.live="pajak"
                    min="0"
                    max="100"
                    oninput="this.value = this.value > 100 ? 100 : Math.max(0, this.value)"
                    class="w-full text-sm"
                />
            </div>

            <hr class="my-3">
            <div class="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span>Rp {{ number_format($this->getTotal(), 0, ',', '.') }}</span>
            </div>

            <x-filament::button
            
            wire:click="simpan"
            color="primary"
            class="w-full mt-4"
            :disabled="collect($cart)->isEmpty() || $this->getTotal() <= 0"
            >
            Simpan
            </x-filament::button>
        </div>
    </div>

{{-- Print Area --}}
<div id="print-area" class="print-area">
    <div class="text-center mb-2">
        <img src="{{ asset('storage/logo-nota.png') }}" class="mx-auto mb-1" style="height: 40px">
        <div class="text-sm font-bold">Renz Kitchen</div>
        <div class="text-xs">Jl. Tirta Tawar No.1</div>
        <div class="text-xs">0811 1111 1111</div>
        <div class="text-xs mb-2">{{ now()->format('d-m-Y H:i:s') }}</div>
    </div>

    @foreach ($cart as $item)
        <div class="flex justify-between text-xs mb-1">
            <div>{{ $item['nama'] }} x{{ $item['qty'] }}</div>
            <div>Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}</div>
        </div>
    @endforeach

    <hr class="my-1 border-t border-dashed">

    <div class="text-xs">
        <div class="flex justify-between">
            <span>Subtotal</span>
            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span>Diskon ({{ $diskon }}%)</span>
            <span>Rp {{ number_format($diskonRp, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span>Pajak ({{ $pajak }}%)</span>
            <span>Rp {{ number_format($pajakRp, 0, ',', '.') }}</span>
        </div>
        <hr class="my-1 border-t border-dashed">
        <div class="flex justify-between font-bold text-sm">
            <span>Total</span>
            <span>Rp {{ number_format($this->getTotal(), 0, ',', '.') }}</span>
        </div>
    </div>
</div>

</x-filament::page>

@script
<script>
    $js('printNota', () => {
        window.print();
    });
</script>
@endscript


@once
    @push('styles')
        <style>
            @media print {
                body * {
                    visibility: hidden !important;
                }

                .print-area, .print-area * {
                    visibility: visible !important;
                }

                .print-area {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    padding: 10px;
                    font-size: 12px;
                    display: block !important;
                }

                @page {
                    size: 80mm auto;
                    margin: 5mm;
                }

                img {
                    max-height: 40px;
                    margin: auto;
                }

                .text-xs {
                    font-size: 10px;
                }

                .text-sm {
                    font-size: 12px;
                }

                .text-md {
                    font-size: 14px;
                }

                .text-lg {
                    font-size: 16px;
                    font-weight: bold;
                }

                hr {
                    border-top: 1px dashed #000;
                }
            }

            /* Sembunyikan print-area saat mode normal (tidak print) */
            .print-area {
                display: none;
            }
        </style>
    @endpush
@endonce
