<?php

namespace App\Filament\PosApp\Resources;

use App\Filament\PosApp\Resources\ProdukResource\Pages;
use App\Filament\PosApp\Resources\ProdukResource\RelationManagers;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Spatie\Image\Image;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Produk & Bahan Baku';

    protected static ?string $navigationLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Select::make('kategori_id')
                ->relationship('kategori', 'nama')
                ->label('Kategori Produk')
                ->searchable()
                ->preload()
                ->placeholder('Pilih Kategori')
                ->required(),
                
                TextInput::make('nama')
                    ->required()
                    ->maxLength(100),

                TextInput::make('harga_jual')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),    

                TextInput::make('stok')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->disabled(fn ($record) => $record?->stok > 0 && !Auth::user()->hasRole('super_admin')),

                Textarea::make('keterangan')
                    ->required(),
   
                FileUpload::make('gambar')
                    ->label('Foto Produk')
                    ->image()
                    ->imageEditor()
                    ->imagePreviewHeight('150')
                    ->maxSize(5120) // 5MB
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->directory('produk-images')
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(fn () => (string) Str::uuid() . '.webp') 
                    ->helperText('Upload gambar produk. Maks. 800x800px, dikompresi otomatis.')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn::make('gambar')->square()->width(50)->height(50),
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('kategori.nama')->label('Kategori'),
                TextColumn::make('stok')->sortable(),
                TextColumn::make('harga_jual')->money('IDR'),

            ])
            ->filters([
                //
                TrashedFilter::make()
                    ->hidden(!Auth::user()->hasRole('super_admin')),
                SelectFilter::make('kategori')
                    ->relationship('kategori', 'nama')
                    ->searchable()
                    ->preload()
                    ->optionsLimit(10)
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
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
}
