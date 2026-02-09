<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('superAdmin', fn (User $user) => $user->role?->slug === 'super_admin');

        Gate::define('accessProfessionalFeatures', fn (User $user) => in_array(
            $user->role?->slug ?? 'starter',
            ['professional', 'enterprise', 'super_admin'],
            true
        ));
    }
}
