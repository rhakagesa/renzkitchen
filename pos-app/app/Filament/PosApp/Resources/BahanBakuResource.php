<?php

namespace App\Filament\PosApp\Resources;

use App\Filament\PosApp\Resources\BahanBakuResource\Pages;
use App\Filament\PosApp\Resources\BahanBakuResource\RelationManagers;
use App\Models\BahanBaku;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;

class BahanBakuResource extends Resource
{
    protected static ?string $model = BahanBaku::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    //protected static ?string $navigationGroup = 'Produk & Bahan Baku';

    protected static ?string $navigationLabel = 'Bahan Baku';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('nama')->required()->maxLength(100),
                Select::make('satuan')
                    ->options([
                        'kg' => 'Kilogram',
                        'gram' => 'Gram',
                        'liter' => 'Liter',
                        'ml' => 'Milliliter',
                        'pcs' => 'Pcs (Pieces)',
                    ])
                    ->required()
                    ->placeholder('Pilih Satuan'),
                TextInput::make('stok')
                    ->numeric()
                    ->default(0)
                    ->disabled(fn ($record) => isset($record) && $record->stok > 0)
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('nama')
                    ->label('Nama Bahan Baku')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('satuan')
                    ->label('Satuan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
                TrashedFilter::make()
                    ->hidden(!Auth::user()->hasRole('super_admin')),
                SelectFilter::make('satuan')
                    ->options([
                        'kg' => 'Kilogram',
                        'gram' => 'Gram',
                        'liter' => 'Liter',
                        'ml' => 'Milliliter',
                        'pcs' => 'Pcs (Pieces)',
                    ])
            ])
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()?->hasRole('super_admin')) {
            return $query->withTrashed();
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBahanBakus::route('/'),
            'create' => Pages\CreateBahanBaku::route('/create'),
            'edit' => Pages\EditBahanBaku::route('/{record}/edit'),
        ];
    }
}
