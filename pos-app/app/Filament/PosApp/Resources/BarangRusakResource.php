<?php

namespace App\Filament\PosApp\Resources;

use App\Filament\PosApp\Resources\BarangRusakResource\Pages;
use App\Filament\PosApp\Resources\BarangRusakResource\RelationManagers;
use App\Models\BahanBaku;
use App\Models\BarangRusak;
use App\Models\Pengeluaran;
use App\Models\Produk;
use Filament\Tables\Filters\Filter;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Livewire\on;

class BarangRusakResource extends Resource
{
    protected static ?string $model = BarangRusak::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';

    protected static ?string $navigationGroup = 'Mutasi Stok';

    protected static ?string $navigationLabel = 'Barang Rusak';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->default(now())
                    ->columnSpanFull(),
                Section::make('Barang Rusak')
                    ->schema([
                    Split::make([
                        Select::make('tipe')
                            ->label('Tipe')
                            ->placeholder('Pilih Tipe')
                            ->options([
                                'produk' => 'Produk',
                                'bahan_baku' => 'Bahan Baku'
                            ])
                            ->required()
                            ->afterStateUpdated(function ($set, $get) {
                                $set('barang_rusak_id', null);
                                $set('jumlah', null);
                                $set('satuan', null);
                                $set('nilai_kerugian', null);
                                $set('total_kerugian', null);
                            })
                            ->live(onBlur: true),
                        Select::make('barang_rusak_id')
                            ->label('Nama Barang')
                            ->placeholder('Pilih Barang')
                            ->disabled(fn ($get) => $get('tipe') == null)
                            ->options(fn ($get) => $get('tipe') == 'produk' ? Produk::query()->orderBy('nama', 'asc')->pluck('nama', 'id') : BahanBaku::query()->orderBy('nama', 'asc')->pluck('nama', 'id'))
                            ->preload()
                            ->searchable()
                            ->required()
                            ->afterStateUpdated(function ($set, $get) {
                                if($get('tipe') == 'produk' && $get('barang_rusak_id')) {
                                    $getProduk = Produk::where('id', $get('barang_rusak_id'))->first();
                                    $set('jumlah', $getProduk->stok);
                                    $set('nilai_kerugian', \number_format($getProduk->harga_jual, 0, '.', ','));
                                    if(!empty($get('jumlah')) && !empty($get('nilai_kerugian'))) {
                                        $jumlahNilaiKerugian = $get('jumlah') *  (int) str_replace(',', '', $get('nilai_kerugian') ?? 0);
                                        $set('total_kerugian', \number_format($jumlahNilaiKerugian, 0, '.', ','));
                                    }
                                } elseif ($get('tipe') == 'bahan_baku' && $get('barang_rusak_id')) {
                                    $getBahanBaku = BahanBaku::where('id', $get('barang_rusak_id'))->first();
                                    $set('jumlah', $getBahanBaku->stok);
                                    $set('satuan', $getBahanBaku->satuan);
                                }
                            })
                            ->live(),
                        TextInput::make('jumlah')
                            ->label('Jumlah')
                            ->disabled(fn ($get) => $get('tipe') == null)
                            ->numeric()
                            ->hint(fn ($set, $get) => 'Stok tersedia: ' . ($get('jumlah') ?? 0))
                            ->suffix(fn ($set, $get) => $get('tipe') == "bahan_baku" && $get('barang_rusak_id') ? $get('satuan') : 'pcs')
                            ->required()
                            ->afterStateUpdated(function ($set, $get) {
                                if(!empty($get('jumlah')) && !empty($get('nilai_kerugian'))) {
                                    $jumlahNilaiKerugian = $get('jumlah') *  (int) str_replace(',', '', $get('nilai_kerugian') ?? 0);
                                    $set('total_kerugian', \number_format($jumlahNilaiKerugian, 0, '.', ','));
                                }
                            })->live()        
                    ])
                ]),
                Section::make('Kerugian')
                    ->schema([
                    Split::make([
                        TextInput::make('nilai_kerugian')
                            ->label('Nominal')
                            ->prefix('Rp ')
                            ->disabled(fn ($get) => $get('tipe') == null)
                            ->suffix(fn ($set, $get) => $get('tipe') == "bahan_baku" && $get('barang_rusak_id') ? "/". $get('satuan') : '/pcs')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->inputMode('decimal')
                            ->default(0)
                            ->numeric()
                            ->afterStateUpdated(function ($set, $get) {
                                $jumlahNilaiKerugian = $get('jumlah') *  (int) str_replace(',', '', $get('nilai_kerugian') ?? 0);
                                $set('total_kerugian', \number_format($jumlahNilaiKerugian, 0, '.', ','));
                            })->live(onBlur: true),
                        TextInput::make('total_kerugian')
                            ->label('Total')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->prefix('Rp ')
                            ->stripCharacters(',')
                            ->inputMode('decimal')
                            ->default(0)
                            ->readOnly()
                    ]) 
                ]),
                TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->required()
                    ->columnSpanFull(),
                Hidden::make('satuan')
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable(),
                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'produk' => 'success',
                        'bahan_baku' => 'primary',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'produk' => 'PRODUK',
                        'bahan_baku' => 'BAHAN BAKU',    
                    }),
                TextColumn::make('nama_barang')
                    ->label('Barang Rusak')
                    ->sortable()
                    ->formatStateUsing(function (string $state, $record) {
                        if($record->tipe == 'bahan_baku') {
                            return $record->bahan_baku->nama;
                        } else {
                            return $record->produk->nama;
                        }
                    }),
                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->sortable()
                    ->formatStateUsing(function (string $state, $record) {
                        $getBarangRusak = null;
                        if($record->tipe == 'bahan_baku') {
                            $getBarangRusak = BahanBaku::where('id', $record->bahan_baku_id)->first();
                            return "$state $getBarangRusak->satuan";
                        } else {
                            $getBarangRusak = Produk::where('id', $record->produk_id)->first();
                            return "$state pcs";
                        }
                    }),
                TextColumn::make('total_kerugian')
                    ->label('Total Kerugian')
                    ->sortable()
                    ->money('idr', true),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->sortable(),
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
                        'produk' => 'Produk',
                        'bahan_baku' => 'Bahan Baku',
                    ])
                ], layout: FiltersLayout::AboveContent)
            ->actions([
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
            'index' => Pages\ListBarangRusaks::route('/'),
            'create' => Pages\CreateBarangRusak::route('/create'),
            'edit' => Pages\EditBarangRusak::route('/{record}/edit'),
        ];
    }
}
