<?php

namespace App\Filament\PosApp\Resources;

use App\Filament\PosApp\Resources\MutasiStokResource\Pages;
use App\Filament\PosApp\Resources\MutasiStokResource\RelationManagers;
use App\Models\BahanBaku;
use App\Models\Kategori;
use App\Models\MutasiStok;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MutasiStokResource extends Resource
{
    protected static ?string $model = MutasiStok::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Mutasi Stok';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Repeater::make('bahan_baku')
                    ->label('Bahan Baku')
                    ->schema([
                        Select::make('bahan_baku_id')
                            ->label('Nama')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->options(
                                BahanBaku::query()
                                    ->orderBy('nama', 'asc')
                                    ->pluck('nama', 'id')
                            )->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->placeholder('Pilih Bahan Baku')
                            ->live(debounce: 700)
                            ->afterStateHydrated(function ($set, $get) {
                                $set('item_bahan_baku', BahanBaku::find($get('bahan_baku_id')));
                                $set('original_qty', $get('qty'));
                            })
                            ->afterStateUpdated(function ($set, $get) {
                                $set('item_bahan_baku', BahanBaku::find($get('bahan_baku_id')));
                                $set('qty', $get('item_bahan_baku')?->stok);
                            }),
                        TextInput::make('qty')
                            ->label('Jumlah')
                            ->suffix(function ($get) {
                                $item = $get('item_bahan_baku');
                                return is_array($item) ? ($item['satuan'] ?? null) : ($item?->satuan ?? null);
                            })
                            ->hint(function ($get) {
                                $item = $get('item_bahan_baku');
                                $stok = is_array($item) ? ($item['stok'] ?? null) : ($item?->stok ?? null);
                                return $stok !== null ? 'Stok tersedia: ' . $stok : null;
                            })
                            ->maxValue(function ($get) {
                                $item = $get('item_bahan_baku');
                                $stok = is_array($item) ? ($item['stok'] ?? null) : ($item?->stok ?? null);
                                $originalQty = $get('original_qty');
                                $currentQty = $get('qty');
                            
                                // Jika sedang edit dan ingin mengurangi jumlah, izinkan
                                if ($originalQty !== null && $currentQty < $originalQty) {
                                    return null; // disable maxValue constraint
                                }
                            
                                return $stok ?? $currentQty;
                            })
                            ->required()
                            ->disabled(fn ($get) => $get('item_bahan_baku') === null)
                            ->numeric()
                            ->live(debounce: 700),
                        Hidden::make('item_bahan_baku'),
                        Hidden::make('original_qty')
                    ])
                    ->columnSpanFull(),
                Select::make('produk_id')
                    ->relationship('produk', 'nama')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->label('Produk')
                    ->placeholder('Pilih Produk')
                    ->columnSpanFull()
                    ->live(),
                TextInput::make('jumlah_produk')
                    ->label('Jumlah Produk')
                    ->required()
                    ->numeric()
                    ->disabled(fn ($get) => $get('produk_id') === null)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->required()
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date(),
                TextColumn::make('produk.nama')
                    ->label('Produksi Menu'),
                TextColumn::make('jumlah_produk')
                    ->label('Jumlah Produksi'),
                TextColumn::make('keterangan')
                    ->label('Keterangan'),
            ])
            ->filters([
                //
                TrashedFilter::make()
                    ->hidden(!Auth::user()->hasRole('super_admin')),
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('tanggal')
                            ->label('Tanggal Produksi')
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tanggal'], fn ($query) => $query->whereDate('created_at', '=', date('Y-m-d', strtotime($data['tanggal']))));
                    }),
                Filter::make('tipe_produk')
                    ->form([
                        Select::make('tipe_produk')
                            ->label('Tipe Produk')
                            ->options(
                                Kategori::query()->pluck('nama', 'id')
                            )
                            ->searchable()
                            ->placeholder('Pilih Tipe Produk') 
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tipe_produk'], function ($query) use ($data) {
                                $query->whereHas('produk', function ($q) use ($data) {
                                    $q->where('kategori_id', $data['tipe_produk']);
                                });
                            });
                    })
                ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->hidden(fn ($record) => $record->deleted_at !== null),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn ($record) => $record->deleted_at !== null),
                Tables\Actions\RestoreAction::make()
                    ->hidden(fn ($record) => $record->deleted_at === null),])
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
            'index' => Pages\ListMutasiStoks::route('/'),
            'create' => Pages\CreateMutasiStok::route('/create'),
            'edit' => Pages\EditMutasiStok::route('/{record}/edit'),
        ];
    }
}
