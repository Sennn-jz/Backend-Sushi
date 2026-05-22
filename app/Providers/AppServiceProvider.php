<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// ✅ Tambahkan import Laravel Sanctum model asli di bawah ini
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;

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
        // ✅ PAKSA Laravel Sanctum untuk selalu menggunakan model MySQL asli bawaan framework
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}