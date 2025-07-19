<?php

namespace App\Filament\PosApp\Resources;

use App\Filament\PosApp\Resources\PendapatanResource\Pages;
use App\Filament\PosApp\Resources\PendapatanResource\RelationManagers;
use App\Models\Pendapatan;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PendapatanResource extends Resource
{
    protected static ?string $model = Pendapatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationGroup = 'Pembukuan';

    protected static ?string $navigationLabel = 'Pendapatan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                DatePicker::make('tanggal')
                    ->required()
                    ->label('Tanggal'),
                Select::make('tipe')
                    ->required()
                    ->label('Tipe')
                    ->options([
                        'penjualan' => 'Penjualan',
                        'lainnya' => 'Lainnya',
                    ])->live(),
                Repeater::make('penjualan_items')
                    ->visible(fn ($get) => $get('tipe') === 'penjualan')
                    ->schema([
                        Select::make('produk_id')
                            ->required()
                            ->preload()
                            ->label('Produk')
                            ->placeholder('Pilih Produk')
                            ->searchable()
                            ->options(
                                Produk::query()
                                    ->orderBy('nama', 'asc')
                                    ->pluck('nama', 'id')
                            )
                            ->afterStateUpdated(function ($set, $get) {
                                $set('produk_item', Produk::find($get('produk_id')));
                                $set('harga', \number_format($get('produk_item')['harga_jual'], 0, '.', ','));
                                self::calculatedSubTotal($set, $get);
                                self::recalculateTotalPenjualan($set, $get);
                            })
                            ->afterStateHydrated(function ($set, $get) {
                                $set('produk_item', Produk::find($get('produk_id')));
                            })
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live(),
                        TextInput::make('qty')
                            ->required()
                            ->label('Jumlah')
                            ->numeric()
                            ->hint(function ($get) {
                                $item = $get('produk_item');
                                $stok = is_array($item) ? ($item['stok'] ?? null) : ($item?->stok ?? null);
                                return $stok !== null ? 'Stok tersedia: ' . $stok : null;
                            })
                            ->minValue(0)
                            ->maxValue(function ($get) {
                                $item = $get('produk_item');
                                $stok = is_array($item) ? ($item['stok'] ?? null) : ($item?->stok ?? null);
                                $originalQty = $get('original_qty');
                                $currentQty = $get('qty');
                            
                                // Jika sedang edit dan ingin mengurangi jumlah, izinkan
                                if ($originalQty !== null && $currentQty < $originalQty) {
                                    return null; // disable maxValue constraint
                                }
                            
                                return $stok ?? $currentQty;
                            })
                            ->afterStateUpdated(function ($set, $get) {
                                self::calculatedSubTotal($set, $get);
                            })->live(debounce: 700),
                        TextInput::make('harga')
                            ->required()
                            ->label('Harga')
                            ->readOnly()
                            ->prefix('Rp '), 
                        TextInput::make('subtotal')
                            ->required()
                            ->label('Sub Total')
                            ->readOnly()
                            ->prefix('Rp '), 
                        Hidden::make('produk_item'),
                        Hidden::make('original_qty')
                    ])
                    ->addAction(function ($set, $get) {
                        self::recalculateTotalPenjualan($set, $get);
                    }),
                Section::make()
                    ->schema([
                    TextInput::make('total')
                        ->required()
                        ->label(fn ($get) => $get('tipe') === 'penjualan' ? 'Total Penjualan' : 'Jumlah Pendapatan')
                        ->prefix('Rp ')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->inputMode('decimal')
                        ->default(0)
                        ->readOnly(fn ($get) => $get('tipe') === 'penjualan')
                        ->afterStateUpdated(function ($set, $get) {
                            self::recalculateGrandTotal($set, $get);
                        })->live(debounce: 700),
                    TextInput::make('diskon')
                        ->required()
                        ->label('Diskon')
                        ->suffix('%')
                        ->default(0)
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->afterStateUpdated(function ($set, $get) {
                            self::recalculateGrandTotal($set, $get);
                        })->live(debounce: 500),
                    TextInput::make('pajak')
                        ->required()
                        ->label('Pajak')
                        ->suffix('%')
                        ->default(0)
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->afterStateUpdated(function ($set, $get) {
                            self::recalculateGrandTotal($set, $get);
                        })->live(debounce: 500),
                    TextInput::make('grand_total')
                        ->required()
                        ->label('Grand Total')
                        ->prefix('Rp ')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->inputMode('decimal')
                        ->default(0)
                        ->readOnly()
                    ])->columnSpan(1),
                TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tipe')
                    ->label('Tipe Pendapatan')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'penjualan' => 'success',
                        'lainnya' => 'primary',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'penjualan' => 'PENJUALAN',
                        'lainnya' => 'LAINNYA',
                    }),
                TextColumn::make('grand_total')
                    ->label('Total Pendapatan')
                    ->sortable()
                    ->searchable()
                    ->money('idr', true),
            ])
            ->filters([
                //
                TrashedFilter::make()
                    ->hidden(!Auth::user()->hasRole('super_admin')),
                Filter::make('tanggal_mulai')
                    ->form([
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tanggal_mulai'], fn ($query) => $query->whereDate('tanggal', '>=', $data['tanggal_mulai']));
                    }),
                Filter::make('tanggal_sampai')
                    ->form([
                        DatePicker::make('tanggal_sampai')
                            ->label('Tanggal Sampai')
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tanggal_sampai'], fn ($query) => $query->whereDate('tanggal', '<=', $data['tanggal_sampai']));
                    }),
                SelectFilter::make('tipe')
                    ->options([
                        'penjualan' => 'Penjualan',
                        'lainnya' => 'Lainnya',
                    ])
                ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make("nota")
                    ->label('Nota')
                    ->icon('heroicon-s-printer')
                    ->hidden(fn ($record) => $record->tipe !== 'penjualan')
                    ->url(fn ($record) => self::getUrl('cetak-nota', ['record' => $record->id])),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->hidden(fn ($record) => $record->deleted_at !== null),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn ($record) => $record->deleted_at !== null),
                Tables\Actions\RestoreAction::make()
                    ->hidden(fn ($record) => $record->deleted_at === null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendapatans::route('/'),
            'create' => Pages\CreatePendapatan::route('/create'),
            'view' => Pages\ViewPendapatan::route('/{record}'),
            'edit' => Pages\EditPendapatan::route('/{record}/edit'),
            'cetak-nota' => Pages\CetakNota::route('/{record}/cetak-nota'),
        ];
    }

    static function recalculateGrandTotal($set, $get): void
    {
        $total = (int) str_replace(',', '', $get('total') ?? 0);
        $diskon = (int) $get('diskon') ?? 0;
        $pajak = (int) $get('pajak') ?? 0;

        $afterDiskon = $total - ($total * ($diskon / 100));
        $afterPajak = $afterDiskon + ($afterDiskon * ($pajak / 100));

        $grandTotal = (int) round($afterPajak);
        $set('grand_total', number_format($grandTotal, 0, '.', ','));
    }

    static function calculatedSubTotal($set, $get): void
    {
        $qty = (int) ($get('qty') ?? 0);
        $harga = (int) (\str_replace(['.', ','], '', $get('harga')) ?? 0);

        $subTotal = $qty * $harga;
        $set('subtotal', number_format($subTotal, 0, '.', ','));
    }

    static function recalculateTotalPenjualan($set, $get): void
    {
        $totalPenjualan = $get('penjualan_items') ?? [];
        $total = 0;

        foreach($totalPenjualan as $item) {
            $subTotal = (int) (\str_replace(['.', ','], '', $item['subtotal']) ?? 0);
            $total += $subTotal;
        }
        
        $set('total', number_format($total, 0, '.', ','));
        
        self::recalculateGrandTotal($set, $get);
    }
}
