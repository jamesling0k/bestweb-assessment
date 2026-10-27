<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Paginator::defaultView('pagination');

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower($request->string('email')->toString()).'|'.$request->ip(),
            );
        });

        RateLimiter::for('api', fn (Request $request) =>
            Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
    }
}
