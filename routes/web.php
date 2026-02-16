<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MarketPulseSnapshotController;
use App\Http\Controllers\Admin\OutreachContactController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WaitlistController as AdminWaitlistController;
use App\Http\Controllers\CacheWarmupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SnapshotController;
use App\Http\Controllers\AiLabCollaborationController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketPulseController;

Route::get('/market-pulse', [MarketPulseController::class, 'index'])->middleware(['auth', 'verified'])->name('market-pulse');

// Cache warmup: call after deploy so first page loads are fast. Requires ?secret=CACHE_WARMUP_SECRET (404 if not set).
Route::get('/warmup', [CacheWarmupController::class, '__invoke'])->name('warmup');

// BigQuery env diagnostic (only when APP_DEBUG=true; remove or disable after debugging)
Route::get('/bigquery-health', function () {
    if (! config('app.debug')) {
        abort(404);
    }
    $keyJson = env('GOOGLE_APPLICATION_CREDENTIALS_JSON');
    $storageApp = storage_path('app');
    $credsPath = $storageApp . '/google-credentials.json';

    return response()->json([
        'project_id_set' => ! empty(trim((string) env('BQ_PROJECT_ID'))),
        'key_json_set' => ! empty($keyJson) && strlen($keyJson) > 0,
        'key_json_length' => is_string($keyJson) ? strlen($keyJson) : 0,
        'storage_app_writable' => is_dir($storageApp) && is_writable($storageApp),
        'credentials_file_exists' => file_exists($credsPath),
    ], 200, ['Content-Type' => 'application/json']);
})->name('bigquery-health');

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/ai-lab', function () {
    return view('ai-lab');
})->name('ai-lab');

Route::get('/ai-lab/collaborate', [AiLabCollaborationController::class, 'show'])->name('ai-lab.collaborate');
Route::post('/ai-lab/collaborate', [AiLabCollaborationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('ai-lab.collaborate.store');

Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store')->middleware('throttle:10,1');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::get('/export/market-pulse-pdf', [ExportController::class, 'marketPulsePdf'])->name('export.market-pulse-pdf');
    Route::post('/export/download', [ExportController::class, 'download'])->name('export.download');
    Route::get('/snapshots/{snapshot}/download', [SnapshotController::class, 'download'])->name('snapshots.download');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    Route::resource('users', UserController::class)->except(['show'])->names('users');

    Route::get('snapshots', [MarketPulseSnapshotController::class, 'index'])->name('snapshots.index');
    Route::get('snapshots/create', [MarketPulseSnapshotController::class, 'create'])->name('snapshots.create');
    Route::post('snapshots', [MarketPulseSnapshotController::class, 'store'])->name('snapshots.store');
    Route::get('snapshots/{snapshot}', [MarketPulseSnapshotController::class, 'edit'])->name('snapshots.edit');
    Route::put('snapshots/{snapshot}', [MarketPulseSnapshotController::class, 'update'])->name('snapshots.update');
    Route::delete('snapshots/{snapshot}', [MarketPulseSnapshotController::class, 'destroy'])->name('snapshots.destroy');

    Route::get('waitlist', [AdminWaitlistController::class, 'index'])->name('waitlist.index');
    Route::get('waitlist/export', [AdminWaitlistController::class, 'export'])->name('waitlist.export');

    Route::get('outreach/board', [OutreachContactController::class, 'board'])->name('outreach.board');
    Route::get('outreach/export', [OutreachContactController::class, 'export'])->name('outreach.export');
    Route::post('outreach/{outreach}/status', [OutreachContactController::class, 'updateStatus'])->name('outreach.update-status');
    Route::resource('outreach', OutreachContactController::class)->except(['show'])->names('outreach');
});

require __DIR__.'/auth.php';
