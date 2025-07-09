<?php

namespace App\Filament\SuperAdmin\Resources\OrderResource\Pages;

use App\Filament\SuperAdmin\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
