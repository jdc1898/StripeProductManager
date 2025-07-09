<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeMeter extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'created',
        'customer_mapping',
        'default_aggregation',
        'display_name',
        'event_name',
        'event_time_window',
        'livemode',
        'status',
        'status_transitions',
        'updated',
        'value_settings',
    ];

    protected $casts = [
        'created' => 'integer',
        'customer_mapping' => 'array',
        'default_aggregation' => 'array',
        'event_time_window' => 'array',
        'livemode' => 'boolean',
        'status_transitions' => 'array',
        'updated' => 'integer',
        'value_settings' => 'array',
    ];
}
