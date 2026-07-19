<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Tambahkan baris ini jika belum ada

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paksa semua URL aset menggunakan HTTPS di server produksi (Railway)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}