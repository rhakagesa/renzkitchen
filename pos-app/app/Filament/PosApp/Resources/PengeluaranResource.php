<?php

namespace App\Filament\PosApp\Resources;

use App\Filament\PosApp\Resources\PengeluaranResource\Pages;
use App\Filament\PosApp\Resources\PengeluaranResource\RelationManagers;
use App\Models\BahanBaku;
use App\Models\Pengeluaran;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class PengeluaranResource extends Resource
{
    protected static ?string $model = Pengeluaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    //protected static ?string $navigationGroup = 'Pembukuan';

    protected static ?string $navigationLabel = 'Pengeluaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                DatePicker::make('tanggal')->required()->default(now()),
                Select::make('tipe')
                    ->options([
                        'beli_bahan_baku' => 'Belanja Bahan Baku',
                        'operasional' => 'Operasional',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required()
                    ->placeholder('Pilih Tipe Pengeluaran')
                    ->live(), 
                    
                Repeater::make('bahan_baku')
                    ->label('List Bahan Baku')
                    ->visible(fn ($get) => $get('tipe') === 'beli_bahan_baku')
                    ->schema([
                        Select::make('bahan_baku_id')
                            ->required()
                            ->preload()
                            ->label('Bahan Baku')
                            ->placeholder('Pilih Bahan Baku')
                            ->options(
                                BahanBaku::query()
                                    ->orderBy('nama', 'asc')
                                    ->pluck('nama', 'id')
                            )
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->searchable()
                            ->columnSpanFull()
                            ->live(),
                        TextInput::make('qty')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->disabled(fn ($get) => $get('bahan_baku_id') === null)
                            ->placeholder('Jumlah Bahan Baku')
                            ->live(debounce: 700)
                            ->afterStateUpdated(function ($set, $get) {
                                self::updateTotalHarga($set, $get);
                            }),
                        TextInput::make('satuan')
                            ->label('Satuan')
                            ->numeric()
                            ->required()
                            ->disabled(fn ($get) => $get('bahan_baku_id') === null)
                            ->suffix(function ($get) {
                                $bahanBaku = BahanBaku::find($get('bahan_baku_id'));
                                return $bahanBaku ? $bahanBaku->satuan : '';
                            })
                            ->placeholder('Satuan Bahan Baku'),
                        TextInput::make('harga_satuan')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->live(debounce: 700)
                            ->disabled(fn ($get) => $get('bahan_baku_id') === null)
                            ->afterStateUpdated(function ($set, $get) {
                                self::updateTotalHarga($set, $get);
                            }),
                        TextInput::make('total_harga')
                            ->label('Total')
                            ->numeric()
                            ->readonly()
                            ->prefix('Rp'),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->dehydrated()
                    ->afterStateUpdated(function ($set, $get) {
                        self::updateJumlahTotal($set, $get);
                    }),

                TextInput::make('jumlah_total')
                    ->label('Jumlah Total')
                    ->numeric()
                    ->required()
                    ->dehydrated()
                    ->readonly(fn ($get) => $get('tipe') === 'beli_bahan_baku')
                    ->prefix('Rp'),

                Textarea::make('keterangan')
                    ->maxLength(255)
                    ->required()
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
                    ->label('Tipe Pengeluaran')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'beli_bahan_baku' => 'success',
                        'operasional' => 'warning',
                        'lainnya' => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'beli_bahan_baku' => 'Bahan Baku',
                        'operasional' => 'Operasional',
                        'lainnya' => 'Lainnya',
                    }),
                TextColumn::make('jumlah_total')
                    ->label('Jumlah Total')
                    ->sortable()
                    ->searchable()
                    ->money('idr', true),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->sortable()
                    ->searchable(),
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
                        'beli_bahan_baku' => 'Beli Bahan Baku',
                        'operasional' => 'Operasional',
                        'lainnya' => 'Lainnya',
                    ])
                ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPengeluarans::route('/'),
            'create' => Pages\CreatePengeluaran::route('/create'),
            'edit' => Pages\EditPengeluaran::route('/{record}/edit'),
            'view' => Pages\ViewPengeluaran::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()?->hasRole('super_admin')) {
            return $query->withTrashed();
        }

        return $query;
    }

    protected static function updateJumlahTotal(callable $set, callable $get): void
    {
        $bahanBakuItems = $get('bahan_baku') ?? [];
        $jumlahTotal = 0;
        
        foreach ($bahanBakuItems as $item) {
            $qty = (int) ($item['qty'] ?? 0);
            $hargaSatuan = (int) ($item['harga_satuan'] ?? 0);
            $totalHarga = $qty * $hargaSatuan;
            $jumlahTotal += $totalHarga;
        }

        $set('jumlah_total', $jumlahTotal);
    }

    protected static function updateTotalHarga(callable $set, callable $get): void
    {
        $qty = (int) ($get('qty') ?? 0);
        $hargaSatuan = (int) ($get('harga_satuan') ?? 0);

        $totalHarga = $qty * $hargaSatuan;

        $set('total_harga', $totalHarga);
    }
}
