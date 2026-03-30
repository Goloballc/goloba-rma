<?php

namespace Goloba\GolobaRMA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RmaDisputeImage extends Model
{
    protected $table = 'rma_dispute_images';

    protected $fillable = [
        'dispute_id',
        'path',
        'original_name',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(RmaDispute::class, 'dispute_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
