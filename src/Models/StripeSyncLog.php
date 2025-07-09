<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StripeSyncLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'error_context' => 'array',
        'last_attempt_at' => 'datetime',
        'succeeded_at' => 'datetime',
    ];

    /**
     * The model that is being synced
     */
    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending syncs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include successful syncs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Check if this sync can be retried
     */
    public function canRetry(): bool
    {
        return $this->status === 'failed' && $this->attempts < 3;
    }

    /**
     * Get a user-friendly status message
     */
    public function getStatusMessage(): string
    {
        return match ($this->status) {
            'pending' => 'Sync in progress...',
            'success' => 'Successfully synced with Stripe',
            'failed' => $this->canRetry()
                ? "Sync failed ({$this->attempts} attempts) - Will retry"
                : "Sync failed ({$this->attempts} attempts) - Manual intervention needed",
            default => 'Unknown status'
        };
    }
}
