<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketPulseSnapshot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketPulseSnapshotController extends Controller
{
    /**
     * List snapshots (by month, newest first).
     */
    public function index(): View
    {
        $snapshots = MarketPulseSnapshot::orderBy('month_date', 'desc')->get();

        return view('admin.snapshots.index', compact('snapshots'));
    }

    /**
     * Show form to create a snapshot (pick month).
     */
    public function create(): View
    {
        return view('admin.snapshots.create');
    }

    /**
     * Store a new snapshot (draft).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'executive_summary' => ['nullable', 'string', 'max:65535'],
        ]);

        $monthDate = Carbon::parse($validated['month'] . '-01')->startOfMonth();

        $existing = MarketPulseSnapshot::where('month_date', $monthDate)->first();
        if ($existing) {
            return redirect()
                ->route('admin.snapshots.edit', $existing)
                ->with('info', 'A snapshot for this month already exists. You can edit it below.');
        }

        MarketPulseSnapshot::create([
            'month_date' => $monthDate,
            'executive_summary' => $validated['executive_summary'] ?? null,
            'published_at' => null,
        ]);

        return redirect()
            ->route('admin.snapshots.index')
            ->with('success', 'Snapshot created. You can edit and publish it from the list.');
    }

    /**
     * Show form to edit a snapshot (executive summary, Save, Publish).
     */
    public function edit(MarketPulseSnapshot $snapshot): View
    {
        return view('admin.snapshots.edit', compact('snapshot'));
    }

    /**
     * Update snapshot (save; optionally publish).
     */
    public function update(Request $request, MarketPulseSnapshot $snapshot): RedirectResponse
    {
        $validated = $request->validate([
            'executive_summary' => ['nullable', 'string', 'max:65535'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $snapshot->executive_summary = $validated['executive_summary'] ?? $snapshot->executive_summary;

        if ($request->boolean('publish')) {
            $snapshot->published_at = $snapshot->published_at ?? now();
        }

        $snapshot->save();

        $message = $request->boolean('publish')
            ? 'Snapshot updated and published.'
            : 'Snapshot saved.';

        return redirect()
            ->route('admin.snapshots.index')
            ->with('success', $message);
    }

    /**
     * Delete a snapshot. After deletion, Market Pulse will show the dynamic ExecutiveSummaryBuilder summary for that month.
     */
    public function destroy(MarketPulseSnapshot $snapshot): RedirectResponse
    {
        $snapshot->delete();

        return redirect()
            ->route('admin.snapshots.index')
            ->with('success', 'Snapshot deleted. That month will now show the auto-generated executive summary on Market Pulse.');
    }
}
