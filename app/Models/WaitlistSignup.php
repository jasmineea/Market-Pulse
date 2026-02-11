<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaitlistSignup extends Model
{
    protected $table = 'waitlist_signups';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'organization',
        'persona_type',
        'operator_type',
        'use_case',
        'interests',
        'notes',
        'source_page',
        'is_duplicate',
    ];

    /**
     * Persona type label from config (for display in admin).
     */
    public function getPersonaTypeLabelAttribute(): ?string
    {
        if (! $this->persona_type) {
            return null;
        }
        return config('terpinsights.persona_types')[$this->persona_type] ?? $this->persona_type;
    }

    /**
     * Operator type label from config (for display in admin).
     */
    public function getOperatorTypeLabelAttribute(): ?string
    {
        if (! $this->operator_type) {
            return null;
        }
        return config('terpinsights.operator_types')[$this->operator_type] ?? $this->operator_type;
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'interests' => 'array',
            'is_duplicate' => 'boolean',
        ];
    }
}
