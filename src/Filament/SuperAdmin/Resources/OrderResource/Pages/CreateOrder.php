<?php

namespace App\Filament\SuperAdmin\Resources\OrderResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\SuperAdmin\Resources\OrderResource;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
