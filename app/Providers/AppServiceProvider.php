<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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
        View::composer('*', function ($view) {
            $routeName = Route::currentRouteName();

            $pageTitles = [
                'home' => 'Home',
                'about' => 'About Us',
                'contact' => 'Contact Us',
            ];

            $pageTitle = $pageTitles[$routeName] ?? 'Dashboard';

            $view->with('pageTitle', $pageTitle);
        });
    }
}
