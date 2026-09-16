<?php

namespace App\Http\View\Composers;

use App\Http\Logic\Index\IndexLogic;
use App\Http\Logic\Index\NewsLogic;
use App\Http\Logic\Index\ProductLogic;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;
use Predis\Client;

class LayoutComposer
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
        // 1: 获取平台配置
        $data['platformInfo'] = [];
        $platformInfo = IndexLogic::getPlatformInfo(1);
        if (isset($platformInfo['code']) && $platformInfo['code'] === 0) {
            $data['platformInfo'] = $platformInfo['data'];
        }

        // 2: 获取产品分类列表
        $data['productCategoryList'] = [];
        $productCategoryList = ProductLogic::getProductCategoryList();
        if (isset($productCategoryList['code']) && $productCategoryList['code'] === 0) {
            $data['productCategoryList'] = $productCategoryList['data'];
        }

        // 3: 获取新闻分类列表
        $data['newsCategoryList'] = [];
        $newsCategoryList = NewsLogic::getNewsCategoryList();
        if (isset($newsCategoryList['code']) && $newsCategoryList['code'] === 0) {
            $data['newsCategoryList'] = $newsCategoryList['data'];
        }

        // 4: 传递数据
        $view->with('platformInfo', $data['platformInfo']);
        $view->with('productCategoryList', $data['productCategoryList']);
        $view->with('newsCategoryList', $data['newsCategoryList']);
    }
}
