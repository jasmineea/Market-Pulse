<?php

namespace App\Http\Controllers;

use App\Mail\WaitlistConfirmation;
use App\Models\WaitlistSignup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WaitlistController extends Controller
{
    /** Use case values allowed in the form (PRD). */
    public const USE_CASES = [
        'policy' => 'Policy & regulatory research',
        'journalism' => 'Journalism & media',
        'cannabis_business' => 'Cannabis business / operations',
        'market_research' => 'Market research & consulting',
        'academic' => 'Academic research',
        'other' => 'Other',
    ];

    /** Interest checkbox values (PRD). */
    public const INTERESTS = [
        'monthly_summaries' => 'Monthly market summaries',
        'historical_trends' => 'Historical trends',
        'exportable_charts' => 'Exportable charts & datasets',
        'custom_analysis' => 'Custom analysis for my organization',
    ];

    /**
     * Store a waitlist signup. Expects JSON for AJAX; returns JSON.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'organization' => ['required', 'string', 'max:255'],
            'use_case' => ['required', 'string', 'in:' . implode(',', array_keys(self::USE_CASES))],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string', 'in:' . implode(',', array_keys(self::INTERESTS))],
            'notes' => ['nullable', 'string', 'max:500'],
            'source_page' => ['nullable', 'string', 'max:64'],
        ]);

        $isDuplicate = WaitlistSignup::where('email', $validated['email'])->exists();
        $validated['is_duplicate'] = $isDuplicate;

        $signup = WaitlistSignup::create($validated);

        try {
            Mail::to($signup->email)->send(new WaitlistConfirmation($signup));
        } catch (\Throwable $e) {
            // Log so you can see why the email didn't send (check storage/logs/laravel.log)
            Log::warning('Waitlist confirmation email failed to send.', [
                'signup_id' => $signup->id,
                'email' => $signup->email,
                'error' => $e->getMessage(),
            ]);
            report($e);
        }

        return response()->json([
            'success' => true,
            'message' => "You're on the waitlist.",
        ]);
    }
}
