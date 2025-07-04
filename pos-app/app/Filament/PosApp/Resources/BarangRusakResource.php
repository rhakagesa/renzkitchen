<?php

namespace App\Filament\PosApp\Resources;

use App\Filament\PosApp\Resources\BarangRusakResource\Pages;
use App\Filament\PosApp\Resources\BarangRusakResource\RelationManagers;
use App\Models\BahanBaku;
use App\Models\BarangRusak;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Select::make('tipe')
                    ->label('Tipe')
                    ->placeholder('Pilih Tipe')
                    ->options([
                        'produk' => 'Produk',
                        'bahan_baku' => 'Bahan Baku'
                    ])
                    ->required()
                    ->live(),
                Select::make('produk_id')
                    ->label('Produk')
                    ->placeholder('Pilih Produk')
                    ->options(
                        Produk::query()
                            ->orderBy('nama', 'asc')
                            ->pluck('nama', 'id')
                    )->visible(fn ($get) => $get('tipe') == 'produk')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(function ($set, $get) {
                        $set('stok', Produk::find($get('produk_id'))->stok);
                    })
                    ->live(),
                Select::make('bahan_baku_id')
                    ->label('Bahan Baku')
                    ->placeholder('Pilih Bahan Baku')
                    ->options(
                        BahanBaku::query()
                            ->orderBy('nama', 'asc')
                            ->pluck('nama', 'id')
                    )->visible(fn ($get) => $get('tipe') == 'bahan_baku')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(function ($set, $get) {
                        $set('stok', BahanBaku::find($get('bahan_baku_id'))->stok);
                    })
                    ->live(),
                TextInput::make('jumlah')
                    ->label('Jumlah Barang Rusak')
                    ->numeric()
                    ->hint(function ($set, $get) {
                        'Stok tersedia: ' . $get('stok');
                    })
                    ->required(),
                TextInput::make('nilai_kerugian')
                    ->label('Nilai Kerugian')
                    ->hint('Apabila nilai kerugian belum diketahui, dapat dikosongkan')
                    ->prefix('Rp ')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->inputMode('decimal')
                    ->default(0)
                    ->numeric()
                    ->afterStateUpdated(function ($set, $get) {
                        $jumlahNilaiKerugian = $get('jumlah') *  (int) str_replace(',', '', $get('nilai_kerugian') ?? 0);
                        $set('total_kerugian', \number_format($jumlahNilaiKerugian, 0, '.', ','));
                    }),
                TextInput::make('total_kerugian')
                    ->label('Total Kerugian')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->inputMode('decimal')
                    ->default(0)
                    ->readOnly(),
                TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->required(),
                Hidden::make('stok')
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
