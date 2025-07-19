<?php

namespace App\Filament\PosApp\Resources\ProdukResource\Pages;

use App\Filament\PosApp\Resources\ProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Image\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditProduk extends EditRecord
{
    protected static string $resource = ProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['gambar']) && !Storage::disk('public')->exists($data['gambar'])) {
            $data['gambar'] = null;
            
        }
        
        return $data;
    }
    
}
