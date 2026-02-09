<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WaitlistController as PublicWaitlistController;
use App\Models\WaitlistSignup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaitlistController extends Controller
{
    /**
     * List waitlist signups (paginated, filter by use_case, latest first).
     */
    public function index(Request $request)
    {
        $query = WaitlistSignup::query()->latest('created_at');

        if ($request->filled('use_case')) {
            $query->where('use_case', $request->input('use_case'));
        }

        $signups = $query->paginate(25)->withQueryString();
        $useCases = PublicWaitlistController::USE_CASES;
        $interests = PublicWaitlistController::INTERESTS;

        return view('admin.waitlist.index', compact('signups', 'useCases', 'interests'));
    }

    /**
     * Export waitlist signups as CSV (same filters as index).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = WaitlistSignup::query()->latest('created_at');

        if ($request->filled('use_case')) {
            $query->where('use_case', $request->input('use_case'));
        }

        $signups = $query->get();

        $filename = 'waitlist_signups_' . now()->format('Y-m-d_His') . '.csv';

        return Response::streamDownload(function () use ($signups) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'email', 'organization', 'use_case', 'interests', 'notes', 'source_page', 'is_duplicate', 'created_at']);
            foreach ($signups as $s) {
                fputcsv($out, [
                    $s->id,
                    $s->email,
                    $s->organization,
                    $s->use_case,
                    is_array($s->interests) ? implode('; ', $s->interests) : '',
                    $s->notes,
                    $s->source_page ?? '',
                    $s->is_duplicate ? '1' : '0',
                    $s->created_at->toIso8601String(),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
