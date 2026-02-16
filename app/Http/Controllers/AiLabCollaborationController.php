<?php

namespace App\Http\Controllers;

use App\Mail\AiLabCollaborationConfirmation;
use App\Models\OutreachContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AiLabCollaborationController extends Controller
{
    /**
     * Source value for AI Lab collaboration requests.
     */
    public const SOURCE_AI_LAB = 'ai_lab_collaboration';

    /**
     * Show the public collaboration form (no auth required).
     */
    public function show(): View
    {
        return view('ai-lab.collaborate', [
            'title' => 'Collaborate with Terp Insights AI Lab',
            'collaborationTypes' => config('terpinsights.ai_lab_collaboration_types', []),
            'areasOfInterest' => config('terpinsights.ai_lab_areas_of_interest', []),
            'timelines' => config('terpinsights.ai_lab_timelines', []),
            'roles' => OutreachContact::ROLES,
        ]);
    }

    /**
     * Store collaboration request (no auth required).
     */
    public function store(Request $request): RedirectResponse
    {
        $collabKeys = array_keys(config('terpinsights.ai_lab_collaboration_types', []));
        $interestKeys = array_keys(config('terpinsights.ai_lab_areas_of_interest', []));
        $timelineKeys = array_keys(config('terpinsights.ai_lab_timelines', []));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(OutreachContact::ROLES)],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'website' => ['nullable', 'url', 'max:500'],
            'message' => ['required', 'string', 'min:20', 'max:65535'],
            'collaboration_types' => ['required', 'array', 'min:1'],
            'collaboration_types.*' => ['string', Rule::in($collabKeys)],
            'areas_of_interest' => ['required', 'array', 'min:1'],
            'areas_of_interest.*' => ['string', Rule::in($interestKeys)],
            'timeline' => ['nullable', 'string', Rule::in($timelineKeys)],
            // Honeypot for spam: should remain empty
            'institutional_dept' => ['nullable', 'string', 'max:1'],
        ]);

        // Honeypot: reject if filled (likely bot)
        if (! empty(trim($validated['institutional_dept'] ?? ''))) {
            return redirect()
                ->route('ai-lab.collaborate')
                ->with('success', 'Thank you. We will review your request and follow up as appropriate.');
        }

        $collabLabels = config('terpinsights.ai_lab_collaboration_types', []);
        $interestLabels = config('terpinsights.ai_lab_areas_of_interest', []);
        $timelineLabels = config('terpinsights.ai_lab_timelines', []);

        $collabText = implode(', ', array_map(fn ($k) => $collabLabels[$k] ?? $k, $validated['collaboration_types']));
        $interestText = implode(', ', array_map(fn ($k) => $interestLabels[$k] ?? $k, $validated['areas_of_interest']));
        $timelineText = isset($validated['timeline']) ? ($timelineLabels[$validated['timeline']] ?? $validated['timeline']) : null;

        $whySelected = "Collaboration type: {$collabText}. Interests: {$interestText}.";
        if ($timelineText) {
            $whySelected .= " Timeline: {$timelineText}.";
        }
        if (! empty($validated['website'] ?? '')) {
            $whySelected .= " Website: {$validated['website']}.";
        }

        $priority = $this->computePriority($validated['collaboration_types']);
        $followUpDate = $this->computeFollowUpDate($priority);

        $contact = OutreachContact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'organization' => $validated['organization'],
            'role' => $validated['role'],
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'source' => self::SOURCE_AI_LAB,
            'status' => 'Inbound Request',
            'response_summary' => $validated['message'],
            'why_selected' => $whySelected,
            'priority' => $priority,
            'follow_up_date' => $followUpDate,
        ]);

        try {
            Mail::to($contact->email)->send(new AiLabCollaborationConfirmation($contact));
        } catch (\Throwable $e) {
            Log::warning('AI Lab collaboration confirmation email failed to send.', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'error' => $e->getMessage(),
            ]);
            report($e);
        }

        return redirect()
            ->route('ai-lab.collaborate')
            ->with('success', 'Thank you for your interest. We will review your collaboration request and follow up at the email you provided.');
    }

    /**
     * Priority logic per PRD:
     * - High if "Faculty sponsorship / MII alignment"
     * - Med if "Research collaboration" or "Pilot partner"
     * - Low else
     */
    private function computePriority(array $collaborationTypes): string
    {
        if (in_array('faculty_sponsorship_mii', $collaborationTypes, true)) {
            return 'High';
        }
        if (in_array('research_collaboration', $collaborationTypes, true) || in_array('pilot_partner', $collaborationTypes, true)) {
            return 'Med';
        }

        return 'Low';
    }

    /**
     * Follow-up date: High +3 days, Med +7 days, Low +14 days.
     */
    private function computeFollowUpDate(string $priority): ?\Carbon\Carbon
    {
        return match ($priority) {
            'High' => now()->addDays(3),
            'Med' => now()->addDays(7),
            default => now()->addDays(14),
        };
    }
}
