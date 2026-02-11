<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OutreachContact extends Model
{
    protected $table = 'outreach_contacts';

    /** Status enum (pipeline stages). */
    public const STATUSES = [
        'Not Contacted',
        'Connection Sent',
        'Connected',
        'DM Sent',
        'Replied',
        'Call Scheduled',
        'Call Completed',
        'Not a Fit',
        'Converted (Waitlist / User)',
    ];

    /** Priority enum. */
    public const PRIORITIES = [
        'High',
        'Med',
        'Low',
    ];

    /** Role enum (contact type). */
    public const ROLES = [
        'Founder',
        'Investor',
        'Operator',
        'Other',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'linkedin_url',
        'persona_type',
        'operator_type',
        'role',
        'organization',
        'location',
        'why_selected',
        'priority',
        'source',
        'status',
        'date_contacted',
        'response_summary',
        'follow_up_date',
        'notes',
    ];

    /**
     * Persona types from config (shared with waitlist).
     */
    public static function personaTypes(): array
    {
        return config('terpinsights.persona_types', []);
    }

    /**
     * Operator types from config (when persona = operator).
     */
    public static function operatorTypes(): array
    {
        return config('terpinsights.operator_types', []);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_contacted' => 'date',
            'follow_up_date' => 'date',
        ];
    }

    /**
     * Scope: contacts due for follow-up (follow_up_date <= today, status not Converted / Not a Fit).
     */
    public function scopeDueForFollowUp(Builder $query): Builder
    {
        return $query->where('follow_up_date', '<=', now()->toDateString())
            ->whereNotIn('status', ['Converted (Waitlist / User)', 'Not a Fit']);
    }
}
