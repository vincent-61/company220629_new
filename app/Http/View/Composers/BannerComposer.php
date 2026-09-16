<?php

namespace App\Http\View\Composers;

use App\Http\Logic\Index\IndexLogic;
use Illuminate\View\View;

class BannerComposer
{
    /**
     * 创建一个 Layout 视图生成器
     */
    public function __construct()
    {

    }

    /**
     * 绑定视图数据
     *
     * @param View $view
     *
     * @author VincentZheng <1092161320@qq.com> 2022-04-20
     */
    public function compose(View $view)
    {
        // 1: 获取banner
        $data['bannerList'] = [];
        $bannerList = IndexLogic::getBannerList(1);
        if (isset($bannerList['code']) && $bannerList['code'] == 0) {
            $data['bannerList'] = $bannerList['data'];
        }

        // 4: 传递数据
        $view->with('bannerList', $data['bannerList']);
    }
}
