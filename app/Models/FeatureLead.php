<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureLead extends Model
{
    use HasFactory;

    protected $table = 'feature_leads';

    protected $fillable = [
        'artist_id',
        'feature',
        'source',
        'contact_value',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'artist_id' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    /**
     * Serializes the lead into the canonical API DTO payload.
     *
     * @return array<string, mixed>
     */
    public function toDto(): array
    {
        return [
            'id' => $this->id,
            'artist_id' => $this->artist_id,
            'feature' => $this->feature,
            'source' => $this->source,
            'contact_value' => $this->contact_value,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
