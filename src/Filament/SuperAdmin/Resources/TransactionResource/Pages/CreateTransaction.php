<?php

namespace App\Filament\SuperAdmin\Resources\TransactionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\SuperAdmin\Resources\TransactionResource;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
}
