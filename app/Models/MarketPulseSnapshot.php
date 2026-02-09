<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MarketPulseSnapshot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'month_date',
        'executive_summary',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'month_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope to only published snapshots.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Whether this snapshot is published.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
