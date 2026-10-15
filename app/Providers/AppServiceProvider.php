<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Listeners\MergeGuestCartOnLogin;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Observers\SlugRedirectObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, MergeGuestCartOnLogin::class);

        Product::observe(SlugRedirectObserver::class);
        Category::observe(SlugRedirectObserver::class);
        Page::observe(SlugRedirectObserver::class);
    }
}
