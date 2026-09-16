<?php

namespace App\Providers;

use App\Http\View\Composers\BannerComposer;
use App\Http\View\Composers\LayoutComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * 注册应用服务
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * 引导应用服务
     *
     * @return void
     */
    public function boot()
    {
        // 使用基于类的生成器
        View::composer('index*', LayoutComposer::class);

        View::composer('wap*', LayoutComposer::class);
        View::composer('wap*', BannerComposer::class);
    }
}
