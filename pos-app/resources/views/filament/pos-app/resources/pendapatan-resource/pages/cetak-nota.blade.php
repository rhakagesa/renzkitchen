<x-filament::page>
    <div class="flex flex-col lg:flex-row gap-4 w-1/2 justify-self-center">
        {{-- Invoice --}}
        <div class="w-full lg:w-[300px] bg-white rounded-xl shadow p-4 min-h-[450px] max-h-[90vh] overflow-y-auto">
            <div class="w-full flex flex-col items-center">
                <img src="{{ asset('storage/logo-nota.png') }}" alt="Logo" style="width: auto; height: 80px;">
                <h3 class="text-md font-semibold">Jln. Tirta Tawar No. 1</h3>
                <p class="text-sm font-medium">0811 1111 1111</p>
                <p class="text-xs">{{date('d-m-Y H:i:s')}}</p>
            </div>
            @foreach ($this->record->penjualanItems as $item)
            <div class="flex items-start justify-between mb-4 border-b pb-2">
                <div class="flex-1 px-2">
                    <p class="font-medium text-sm leading-tight">{{ $item->produk->nama }} (Rp {{ number_format($item->harga, 0, ',', '.') }})</p>
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-sm w-6 text-center">x {{ $item->qty }}</p>
                    </div>
                </div>
            
                {{-- Kanan: Total Harga --}}
                <div class="text-right text-sm font-semibold min-w-[80px]">
                    Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}
                </div>
            </div>
            @endforeach
            
            @php
                $subtotal = collect($this->record->penjualanItems)->sum(fn ($item) => $item['harga'] * $item['qty']);
                $diskonRp = ($subtotal * $this->record->diskon) / 100;
                $setelahDiskon = $subtotal - $diskonRp;
                $pajakRp = ($setelahDiskon * $this->record->pajak) / 100;
            @endphp

            <div class="text-sm text-gray-600 mb-1">Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
            <div class="text-sm text-gray-600 mb-1">Diskon: Rp {{ number_format($diskonRp, 0, ',', '.') }} ({{ $this->record->diskon }}%)</div>
            <div class="text-sm text-gray-600 mb-1">Pajak: Rp {{ number_format($pajakRp, 0, ',', '.') }} ({{ $this->record->pajak }}%)</div>

            <hr class="my-3">
            <div class="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span>Rp {{ number_format($this->record->grand_total, 0, ',', '.') }}</span>
            </div>

            <x-filament::button
            
            onclick="window.print()"
            color="primary"
            class="w-full mt-4"
            icon="heroicon-s-printer"
            >
            Cetak
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

    @foreach ($this->record->penjualanItems as $item)
        <div class="flex justify-between text-xs mb-1">
            <div>{{ $item->produk->nama }} ({{ $item->qty }} * Rp {{ number_format($item->harga, 0, ',', '.')  }})</div>
            <div>Rp {{ number_format($item->harga * $item->qty, 0, ',', '.') }}</div>
        </div>
    @endforeach

    <hr class="my-1 border-t border-dashed">

    <div class="text-xs">
        <div class="flex justify-between">
            <span>Subtotal</span>
            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span>Diskon ({{ $this->record->diskon }}%)</span>
            <span>Rp {{ number_format($diskonRp, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span>Pajak ({{ $this->record->pajak }}%)</span>
            <span>Rp {{ number_format($pajakRp, 0, ',', '.') }}</span>
        </div>
        <hr class="my-1 border-t border-dashed">
        <div class="flex justify-between font-bold text-sm">
            <span>Total</span>
            <span>Rp {{ number_format($this->record->grand_total, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

</x-filament::page>

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
