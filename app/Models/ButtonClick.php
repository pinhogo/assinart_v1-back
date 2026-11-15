<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ButtonClick extends Model
{
    use HasFactory;

    protected $table = 'button_clicks';

    protected $fillable = [
        'button_id',
        'visitor_id',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    public function button(): BelongsTo
    {
        return $this->belongsTo(Button::class, 'button_id');
    }
}
