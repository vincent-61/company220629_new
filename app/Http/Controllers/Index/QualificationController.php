<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use App\Http\Logic\Index\IndexLogic;
use App\Http\Logic\Index\QualificationLogic;
use Illuminate\Http\Request;

class QualificationController extends Controller
{
    private $qualificationCategoryList;

    public function __construct() {
        // 1: 获取资质分类列表
        $qualificationCategoryList = [];
        $list = QualificationLogic::getQualificationCategoryList();
        if (isset($list['code']) && $list['code'] === 0 && !empty($list['data'])) {
            $qualificationCategoryList = $list['data'];
        }
        $this->qualificationCategoryList = $qualificationCategoryList;
    }

    public function category(Request $request, $categoryId = 0)
    {
        // 1: 获取资质分类列表
        $data['qualificationCategoryList'] = $this->qualificationCategoryList;

        // 2: 获取分类信息
        $data['qualificationCategoryInfo'] = [];
        $qualificationCategoryInfo = QualificationLogic::getQualificationCategoryInfo($categoryId);
        if (isset($qualificationCategoryInfo['code']) && $qualificationCategoryInfo['code'] == 0 && !empty($qualificationCategoryInfo['data'])) {
            $data['qualificationCategoryInfo'] = $qualificationCategoryInfo['data'];
            $categoryId = $data['qualificationCategoryInfo']['category_id'];
        }

        // 3: 获取分类的信息
        $page = $request->get('page', 1);
        $data['nowPage'] = $page;
        $data['pageCount'] = 1;
        $data['qualificationList'] = [];
        $qualificationList = QualificationLogic::getQualificationList($categoryId, $page, 12);
        if (isset($qualificationList['code']) && $qualificationList['code'] == 0 && !empty($qualificationList['data']['pageCount']) && !empty($qualificationList['data']['qualificationList'])) {
            $data['pageCount'] = $qualificationList['data']['pageCount'];
            $data['qualificationList'] = $qualificationList['data']['qualificationList'];
        }

        return view('index.qualification.category', $data);
    }

    public function qualification(Request $request, $qualificationId = 0)
    {
        // 1: 获取资质分类列表
        $data['qualificationCategoryList'] = $this->qualificationCategoryList;

        // 2: 获取产品信息
        $data['qualificationInfo']  = [];
        $qualificationInfo = QualificationLogic::getQualificationDetail($qualificationId);
        if (isset($qualificationInfo['code']) && $qualificationInfo['code'] == 0 && !empty($qualificationInfo['data'])) {
            $data['qualificationInfo'] = $qualificationInfo['data'];
        } else {
            return view('404');
        }

        // 3: 获取分类信息
        $data['qualificationCategoryInfo'] = [];
        $qualificationCategoryInfo = QualificationLogic::getQualificationCategoryInfo($data['qualificationInfo']['category_id']);
        if (isset($qualificationCategoryInfo['code']) && $qualificationCategoryInfo['code'] == 0 && !empty($qualificationCategoryInfo['data'])) {
            $data['qualificationCategoryInfo'] = $qualificationCategoryInfo['data'];
        }

        // 4: 获取上一个和下一个产品
        $data['nearQualificationList'] = [];
        $nearQualificationList = QualificationLogic::getNearQualificationList($qualificationId);
        if (isset($nearQualificationList['code']) && $nearQualificationList['code'] == 0 && !empty($nearQualificationList['data'])) {
            $data['nearQualificationList'] = $nearQualificationList['data'];
        }

        // 5: 获取相关产品
        $data['relateQualificationList'] = [];
        $relateQualificationList = QualificationLogic::getRelateQualificationList($qualificationId, $data['qualificationInfo']['category_id']);
        if (isset($relateQualificationList['code']) && $relateQualificationList['code'] == 0 && !empty($relateQualificationList['data'])) {
            $data['relateQualificationList'] = $relateQualificationList['data'];
        }

        return view('index.qualification.qualification', $data);
    }
}
