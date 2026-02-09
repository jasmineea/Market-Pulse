<?php

namespace App\Http\Controllers;

use App\Models\MarketPulseSnapshot;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public (authenticated) snapshot actions: view printable version, download as PDF.
 */
class SnapshotController extends Controller
{
    /**
     * Show a print-friendly page for the snapshot (user can Print → Save as PDF).
     */
    public function download(MarketPulseSnapshot $snapshot): View
    {
        // Only allow published snapshots
        if ($snapshot->published_at === null) {
            abort(404);
        }

        return view('snapshots.download', compact('snapshot'));
    }
}
