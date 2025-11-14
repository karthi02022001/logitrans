<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Register custom Blade directive for permission checking
        Blade::if('can', function ($permission) {
            return auth()->check() && (
                auth()->user()->hasRole('super_admin') ||
                auth()->user()->hasPermission($permission)
            );
        });

        // Register custom Blade directive for role checking
        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // Register custom Blade directive for checking any of multiple permissions
        Blade::if('canany', function (...$permissions) {
            if (!auth()->check()) {
                return false;
            }

            if (auth()->user()->hasRole('super_admin')) {
                return true;
            }

            return auth()->user()->hasAnyPermission($permissions);
        });
    }
}
