<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

use App\Models\Newsbar;
use App\Models\SubscriptionPlan;
use App\Models\Testimonial;
use App\Models\BusinessSetting;

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
        // Share business_settings and news with all views
        // $business_settings = BusinessSetting::first();
        // $testimonials = Testimonial::all();
        // $news = Newsbar::all();

        // View::share([
        //     'business_settings' => $business_settings,
        //     'news' => $news,
        //     'testimonials' => $testimonials,
        // ]);
    }
}
