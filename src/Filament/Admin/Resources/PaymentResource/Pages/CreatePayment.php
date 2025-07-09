<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Admin\Resources\PaymentResource;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
