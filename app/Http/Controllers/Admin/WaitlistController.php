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
     * List waitlist signups (paginated, filter by use_case, persona_type, operator_type; latest first).
     */
    public function index(Request $request)
    {
        $query = WaitlistSignup::query()->latest('created_at');

        if ($request->filled('use_case')) {
            $query->where('use_case', $request->input('use_case'));
        }

        if ($request->filled('persona_type')) {
            $query->where('persona_type', $request->input('persona_type'));
        }

        if ($request->filled('operator_type')) {
            $query->where('operator_type', $request->input('operator_type'));
        }

        $signups = $query->paginate(25)->withQueryString();
        $useCases = PublicWaitlistController::USE_CASES;
        $interests = PublicWaitlistController::INTERESTS;
        $personaTypes = config('terpinsights.persona_types', []);
        $operatorTypes = config('terpinsights.operator_types', []);

        return view('admin.waitlist.index', compact('signups', 'useCases', 'interests', 'personaTypes', 'operatorTypes'));
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

        if ($request->filled('persona_type')) {
            $query->where('persona_type', $request->input('persona_type'));
        }

        if ($request->filled('operator_type')) {
            $query->where('operator_type', $request->input('operator_type'));
        }

        $signups = $query->get();

        $filename = 'waitlist_signups_' . now()->format('Y-m-d_His') . '.csv';

        return Response::streamDownload(function () use ($signups) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'email', 'organization', 'persona_type', 'operator_type', 'use_case', 'interests', 'notes', 'source_page', 'is_duplicate', 'created_at']);
            foreach ($signups as $s) {
                fputcsv($out, [
                    $s->id,
                    $s->email,
                    $s->organization,
                    $s->persona_type ?? '',
                    $s->operator_type ?? '',
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
