<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeTestClockUser extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_test_clock_id',
        'frozen_timestamp',
    ];

    protected $casts = [
        'frozen_timestamp' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
