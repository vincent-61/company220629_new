<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 向所有视图共享变量
        View::share('title', config('app.name')); // 标题
        View::share('staticAdminUrl', '/static_admin/'); // 静态资源
        View::share('staticUrl', '/static/'); // 静态资源
        View::share('staticWapUrl', '/static_wap/'); // 静态资源
    }
}
