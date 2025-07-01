<?php

namespace App\Filament\PosApp\Resources\UserResource\Pages;

use App\Filament\PosApp\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
