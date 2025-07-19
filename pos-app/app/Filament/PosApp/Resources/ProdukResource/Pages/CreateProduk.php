<?php

namespace App\Filament\PosApp\Resources\ProdukResource\Pages;

use App\Filament\PosApp\Resources\ProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Image\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateProduk extends CreateRecord
{
    protected static string $resource = ProdukResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['gambar'])) {
            $path = storage_path('app/public/' . $data['gambar']);

            if (file_exists($path)) {
                Image::load($path)
                    ->format('webp')
                    ->width(800)
                    ->height(800)
                    ->optimize()
                    ->save($path);
            }
        }

        return $data;
    }
}
