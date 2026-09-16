<?php

namespace App\Http\Controllers\Wap;

use App\Http\Controllers\Controller;
use App\Http\Logic\Index\IndexLogic;
use App\Http\Logic\Index\NewsLogic;
use App\Http\Logic\Index\ProductLogic;
use App\Http\Logic\Index\QualificationLogic;

class IndexController extends Controller {

    public function index()
    {
        // 1: 获取热门产品
        $data['recommendProductList'] = [];
        $recommendProductList = ProductLogic::getRecommendProductList();
        if (isset($recommendProductList['code']) && $recommendProductList['code'] == 0) {
            $data['recommendProductList'] = $recommendProductList['data'];
        }

        // 2: 获取优势品牌
        $data['qualificationList1'] = [];
        $qualificationList1 = QualificationLogic::getQualificationListByCategoryName('优势品牌');
        if (isset($qualificationList1['code']) && $qualificationList1['code'] == 0) {
            $data['qualificationList1'] = $qualificationList1['data'];
        }

        // 3: 获取资质认证
        $data['qualificationList2'] = [];
        $qualificationList2 = QualificationLogic::getQualificationListByCategoryName('资质认证');
        if (isset($qualificationList2['code']) && $qualificationList2['code'] == 0) {
            $data['qualificationList2'] = $qualificationList2['data'];
        }

        // 4: 获取新闻列表
        $data['newsList'] = [];
        $newsList1 = NewsLogic::getNewsList(0);
        if (isset($newsList1['code']) && $newsList1['code'] == 0 && !empty($newsList1['data']['newsList'])) {
            $data['newsList'] = $newsList1['data']['newsList'];
        }

        // dump($data);die;

        return view('wap.index.index', $data);
    }

    public function about()
    {
        return view('wap.index.about');
    }

    public function contact()
    {
        return view('wap.index.contact');
    }

}
