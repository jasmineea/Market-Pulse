<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OutreachContactController extends Controller
{
    /**
     * List contacts with filters (status, role, priority, organization, follow_ups_only) and optional sort.
     */
    public function index(Request $request): View
    {
        $query = OutreachContact::query()->latest('updated_at');

        if ($request->boolean('follow_ups_only')) {
            $query->dueForFollowUp();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('organization')) {
            $query->where('organization', 'like', '%' . $request->input('organization') . '%');
        }

        $sort = $request->input('sort', 'updated_at');
        $direction = $request->input('direction', 'desc');
        if (in_array($sort, ['name', 'status', 'role', 'priority', 'follow_up_date', 'date_contacted', 'updated_at'], true)) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        }

        $contacts = $query->paginate(20)->withQueryString();

        $metrics = $this->computeMetrics();

        return view('admin.outreach.index', [
            'contacts' => $contacts,
            'statuses' => OutreachContact::STATUSES,
            'priorities' => OutreachContact::PRIORITIES,
            'roles' => OutreachContact::ROLES,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Show create contact form.
     */
    public function create(): View
    {
        return view('admin.outreach.create', [
            'statuses' => OutreachContact::STATUSES,
            'priorities' => OutreachContact::PRIORITIES,
            'roles' => OutreachContact::ROLES,
        ]);
    }

    /**
     * Store a new contact.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateContact($request);

        OutreachContact::create($validated);

        return redirect()
            ->route('admin.outreach.index')
            ->with('success', 'Contact created successfully.');
    }

    /**
     * Show edit contact form.
     */
    public function edit(OutreachContact $outreach): View
    {
        return view('admin.outreach.edit', [
            'contact' => $outreach,
            'statuses' => OutreachContact::STATUSES,
            'priorities' => OutreachContact::PRIORITIES,
            'roles' => OutreachContact::ROLES,
        ]);
    }

    /**
     * Update a contact.
     */
    public function update(Request $request, OutreachContact $outreach): RedirectResponse
    {
        $validated = $this->validateContact($request);

        $outreach->update($validated);

        return redirect()
            ->route('admin.outreach.index')
            ->with('success', 'Contact updated successfully.');
    }

    /**
     * Update only status (for Kanban / quick change).
     */
    public function updateStatus(Request $request, OutreachContact $outreach): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', OutreachContact::STATUSES)],
        ]);
        $outreach->update(['status' => $request->input('status')]);

        $back = $request->input('back', route('admin.outreach.index'));
        return redirect($back)->with('success', 'Status updated.');
    }

    /**
     * Delete a contact.
     */
    public function destroy(OutreachContact $outreach): RedirectResponse
    {
        $outreach->delete();

        return redirect()
            ->route('admin.outreach.index')
            ->with('success', 'Contact deleted successfully.');
    }

    /**
     * Kanban board view: contacts grouped by status.
     */
    public function board(): View
    {
        $contacts = OutreachContact::query()->orderBy('updated_at', 'desc')->get();
        $contactsByStatus = [];
        foreach (OutreachContact::STATUSES as $status) {
            $contactsByStatus[$status] = $contacts->where('status', $status)->values();
        }

        return view('admin.outreach.board', [
            'contactsByStatus' => $contactsByStatus,
            'statuses' => OutreachContact::STATUSES,
        ]);
    }

    /**
     * Export contacts as CSV (same filters as index).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = OutreachContact::query()->latest('updated_at');

        if ($request->boolean('follow_ups_only')) {
            $query->dueForFollowUp();
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->filled('organization')) {
            $query->where('organization', 'like', '%' . $request->input('organization') . '%');
        }

        $contacts = $query->get();
        $filename = 'outreach_contacts_' . now()->format('Y-m-d_His') . '.csv';

        return Response::streamDownload(function () use ($contacts) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id', 'name', 'linkedin_url', 'role', 'organization', 'location', 'why_selected',
                'priority', 'source', 'status', 'date_contacted', 'response_summary', 'follow_up_date',
                'notes', 'created_at', 'updated_at',
            ]);
            foreach ($contacts as $c) {
                fputcsv($out, [
                    $c->id,
                    $c->name,
                    $c->linkedin_url,
                    $c->role,
                    $c->organization,
                    $c->location,
                    $c->why_selected,
                    $c->priority,
                    $c->source,
                    $c->status,
                    $c->date_contacted?->format('Y-m-d'),
                    $c->response_summary,
                    $c->follow_up_date?->format('Y-m-d'),
                    $c->notes,
                    $c->created_at->toIso8601String(),
                    $c->updated_at->toIso8601String(),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Validation rules for store/update.
     *
     * @return array<string, mixed>
     */
    private function validateContact(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'linkedin_url' => ['required', 'string', 'url', 'max:500'],
            'role' => ['required', 'string', 'in:'.implode(',', OutreachContact::ROLES)],
            'organization' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'why_selected' => ['nullable', 'string', 'max:65535'],
            'priority' => ['nullable', 'string', 'in:'.implode(',', OutreachContact::PRIORITIES)],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', OutreachContact::STATUSES)],
            'date_contacted' => ['nullable', 'date'],
            'response_summary' => ['nullable', 'string', 'max:65535'],
            'follow_up_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);
    }

    /**
     * Compute outreach metrics for dashboard cards.
     *
     * @return array<string, int|float>
     */
    private function computeMetrics(): array
    {
        $total = OutreachContact::count();
        $dmSent = OutreachContact::whereIn('status', [
            'DM Sent', 'Replied', 'Call Scheduled', 'Call Completed', 'Not a Fit', 'Converted (Waitlist / User)',
        ])->count();
        $replies = OutreachContact::whereIn('status', [
            'Replied', 'Call Scheduled', 'Call Completed', 'Not a Fit', 'Converted (Waitlist / User)',
        ])->count();
        $callsScheduled = OutreachContact::where('status', 'Call Scheduled')->count();
        $callsCompleted = OutreachContact::where('status', 'Call Completed')->count();
        $converted = OutreachContact::where('status', 'Converted (Waitlist / User)')->count();

        $replyRate = $dmSent > 0 ? round(($replies / $dmSent) * 100, 1) : 0.0;
        $callRate = $replies > 0 ? round(($callsScheduled / $replies) * 100, 1) : 0.0;
        $conversionRate = $total > 0 ? round(($converted / $total) * 100, 1) : 0.0;

        return [
            'total_contacts' => $total,
            'dm_sent' => $dmSent,
            'replies' => $replies,
            'calls_scheduled' => $callsScheduled,
            'calls_completed' => $callsCompleted,
            'converted' => $converted,
            'reply_rate' => $replyRate,
            'call_rate' => $callRate,
            'conversion_rate' => $conversionRate,
        ];
    }
}
