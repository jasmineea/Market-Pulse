<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Centralized plan-based permission logic for Starter vs Professional/Enterprise.
 * Unauthenticated or missing role defaults to most restrictive (Starter behavior).
 */
class PlanGate
{
    /** Role slugs that have full Professional-level access. */
    private const PRO_ROLES = ['professional', 'enterprise', 'super_admin'];

    /**
     * Returns the number of months Starter can access (current + prior). Pro+ gets unlimited (0 = no limit).
     */
    public function allowedMonthsWindow(?User $user): int
    {
        if ($this->hasProAccess($user)) {
            return 0; // 0 = unlimited
        }
        return config('terpinsights.starter_allowed_months', 3);
    }

    /**
     * Whether the given month (YYYY-MM) is within the user's allowed window.
     */
    public function isMonthAllowed(string $monthParam, ?User $user): bool
    {
        if ($this->hasProAccess($user)) {
            return true;
        }
        $window = $this->allowedMonthsWindow($user);
        $allowed = $this->getAllowedMonthValues($window);
        return in_array($monthParam, $allowed, true);
    }

    /**
     * Filters all months to only those the user can access.
     *
     * @param  array<string, string>  $allMonths  ['Y-m' => 'Month Label', ...]
     * @return array<string, string>
     */
    public function getAllowedMonths(array $allMonths, ?User $user): array
    {
        if ($this->hasProAccess($user)) {
            return $allMonths;
        }
        $allowedValues = $this->getAllowedMonthValues($this->allowedMonthsWindow($user));
        return array_intersect_key($allMonths, array_flip($allowedValues));
    }

    /**
     * Split all months into allowed and locked for dropdown display.
     *
     * @param  array<string, string>  $allMonths  ['Y-m' => 'Month Label', ...]
     * @return array{allowed: array<string, string>, locked: array<string, string>}
     */
    public function partitionMonths(array $allMonths, ?User $user): array
    {
        if ($this->hasProAccess($user)) {
            return ['allowed' => $allMonths, 'locked' => []];
        }
        $allowedValues = $this->getAllowedMonthValues($this->allowedMonthsWindow($user));
        $allowed = [];
        $locked = [];
        foreach ($allMonths as $ym => $label) {
            if (in_array($ym, $allowedValues, true)) {
                $allowed[$ym] = $label;
            } else {
                $locked[$ym] = $label;
            }
        }
        return ['allowed' => $allowed, 'locked' => $locked];
    }

    /**
     * Get the latest allowed month for the user (to redirect Starter if they request a locked month).
     * If $allMonths is provided, returns the newest month that exists in data and is allowed.
     *
     * @param  array<string, string>  $allMonths
     */
    public function getLatestAllowedMonth(?User $user, array $allMonths = []): ?string
    {
        if (! empty($allMonths)) {
            $allowed = $this->getAllowedMonths($allMonths, $user);
            $keys = array_keys($allowed);
            return $keys[0] ?? null;
        }
        $allowed = $this->getAllowedMonthValues($this->allowedMonthsWindow($user));
        return $allowed[0] ?? null;
    }

    public function canAccessCountyData(?User $user): bool
    {
        return $this->hasProAccess($user);
    }

    public function canDownloadFullPdf(?User $user): bool
    {
        return $this->hasProAccess($user);
    }

    public function canExportRegional(?User $user): bool
    {
        return $this->hasProAccess($user);
    }

    /**
     * Max items to show in Market Pulse History table. Starter = 3, Pro+ = unlimited.
     */
    public function historyLimit(?User $user): int
    {
        if ($this->hasProAccess($user)) {
            return 0; // 0 = no limit
        }
        return config('terpinsights.starter_history_limit', 3);
    }

    /**
     * Alias for canAccessCountyData for view clarity (Dispensary by County chart).
     */
    public function canViewCountyChart(?User $user): bool
    {
        return $this->canAccessCountyData($user);
    }

    private function hasProAccess(?User $user): bool
    {
        $slug = $user?->role?->slug ?? 'starter';
        return in_array($slug, self::PRO_ROLES, true);
    }

    /**
     * Returns allowed month values (Y-m) newest first. For unlimited, returns empty (caller treats as all allowed).
     *
     * @return list<string>
     */
    private function getAllowedMonthValues(int $window): array
    {
        if ($window <= 0) {
            return [];
        }
        $months = [];
        $d = Carbon::now()->startOfMonth();
        for ($i = 0; $i < $window; $i++) {
            $months[] = $d->copy()->subMonths($i)->format('Y-m');
        }
        return $months;
    }
}
