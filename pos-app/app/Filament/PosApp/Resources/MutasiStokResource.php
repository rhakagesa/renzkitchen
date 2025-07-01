<?php

namespace App\Filament\PosApp\Resources;

use App\Filament\PosApp\Resources\MutasiStokResource\Pages;
use App\Filament\PosApp\Resources\MutasiStokResource\RelationManagers;
use App\Models\BahanBaku;
use App\Models\MutasiStok;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                            ->live()
                            ->afterStateUpdated(fn ($set, $get) => $set('qty', BahanBaku::find($get('bahan_baku_id'))->stok)),
                        TextInput::make('qty')
                            ->label('Jumlah')
                            ->required()
                            ->disabled(fn ($get) => $get('bahan_baku_id') === null)
                            ->hint(fn ($get) => 'Stok tersedia: ' . BahanBaku::find($get('bahan_baku_id'))?->stok)
                            ->numeric(),
                    ])
                    ->columnSpanFull(),
                Select::make('produk_id')
                    ->relationship('produk', 'nama')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->label('Produk')
                    ->placeholder('Pilih Produk')
                    ->columnSpanFull(),
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
            'index' => Pages\ListMutasiStoks::route('/'),
            'create' => Pages\CreateMutasiStok::route('/create'),
            'edit' => Pages\EditMutasiStok::route('/{record}/edit'),
        ];
    }
}
