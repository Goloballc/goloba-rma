<?php

namespace Goloba\GolobaRMA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RmaDispute extends Model
{
    protected $table = 'rma_disputes';

    protected $fillable = [
        'rma_id',
        'seller_id',
        'observations',
        'admin_resolution',
        'admin_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function rma(): BelongsTo
    {
        return $this->belongsTo(RMA::class, 'rma_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(RmaDisputeImage::class, 'dispute_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->admin_resolution === null;
    }

    public function isApproved(): bool
    {
        return $this->admin_resolution === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->admin_resolution === 'rejected';
    }
}
