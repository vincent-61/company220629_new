<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use App\Http\Logic\Index\IndexLogic;
use App\Http\Logic\Index\NewsLogic;
use App\Http\Logic\Index\ProductLogic;
use App\Http\Logic\Index\QualificationLogic;

class IndexController extends Controller {

    public function index()
    {
        // 1: 获取banner
        $data['bannerList'] = [];
        $bannerList = IndexLogic::getBannerList(1);
        if (isset($bannerList['code']) && $bannerList['code'] == 0) {
            $data['bannerList'] = $bannerList['data'];
        }

        // 2: 获取热门产品
        $data['recommendProductList'] = [];
        $recommendProductList = ProductLogic::getRecommendProductList();
        if (isset($recommendProductList['code']) && $recommendProductList['code'] == 0) {
            $data['recommendProductList'] = $recommendProductList['data'];
        }

        // 3: 获取优势品牌
        $data['qualificationList1'] = [];
        $qualificationList1 = QualificationLogic::getQualificationListByCategoryName('优势品牌');
        if (isset($qualificationList1['code']) && $qualificationList1['code'] == 0) {
            $data['qualificationList1'] = $qualificationList1['data'];
        }

        // 4: 获取资质认证
        $data['qualificationList2'] = [];
        $qualificationList2 = QualificationLogic::getQualificationListByCategoryName('资质认证');
        if (isset($qualificationList2['code']) && $qualificationList2['code'] == 0) {
            $data['qualificationList2'] = $qualificationList2['data'];
        }

        // 5: 获取企业新闻
        $data['newsList1'] = [];
        $newsList1 = NewsLogic::getNewsListByCategoryName("企业新闻");
        if (isset($newsList1['code']) && $newsList1['code'] == 0) {
            $data['newsList1'] = $newsList1['data'];
        }

        // 6: 获取行业资讯
        $data['newsList2'] = [];
        $newsList2 = NewsLogic::getNewsListByCategoryName("行业资讯");
        if (isset($newsList2['code']) && $newsList2['code'] == 0) {
            $data['newsList2'] = $newsList2['data'];
        }

        // dump($data);die;

        return view('index.index.index', $data);
    }

    public function about()
    {
        return view('index.index.about');
    }

    public function contact()
    {
        return view('index.index.contact');
    }

}
