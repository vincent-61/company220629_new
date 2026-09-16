<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use App\Http\Logic\Index\IndexLogic;
use App\Http\Logic\Index\NewsLogic;
use Illuminate\Http\Request;

class NewsController extends Controller
{

    public function category(Request $request, $categoryId = 0)
    {
        // 1: 获取分类信息
        $data['newsCategoryInfo'] = [];
        if (!empty($categoryId)) {
            $newsCategoryInfo = NewsLogic::getNewsCategoryInfo($categoryId);
            if (isset($newsCategoryInfo['code']) && $newsCategoryInfo['code'] == 0 && !empty($newsCategoryInfo['data'])) {
                $data['newsCategoryInfo'] = $newsCategoryInfo['data'];
            }
        }

        // 2: 获取分类的信息
        $page = $request->get('page', 1);
        $data['nowPage'] = $page;
        $data['pageCount'] = 1;
        $data['newsList'] = [];
        $newsList = NewsLogic::getNewsList($categoryId, $page, 15);
        if (isset($newsList['code']) && $newsList['code'] == 0 && !empty($newsList['data']['pageCount']) && !empty($newsList['data']['newsList'])) {
            $data['pageCount'] = $newsList['data']['pageCount'];
            $data['newsList'] = $newsList['data']['newsList'];
        }

        return view('index.news.category', $data);
    }

    public function news(Request $request, $newsId = 0)
    {
        // 1: 获取产品信息
        $data['newsInfo']  = [];
        $newsInfo = NewsLogic::getNewsDetail($newsId);
        if (isset($newsInfo['code']) && $newsInfo['code'] == 0 && !empty($newsInfo['data'])) {
            $data['newsInfo'] = $newsInfo['data'];
        } else {
            return view('404');
        }

        // 2: 获取分类信息
        $data['newsCategoryInfo'] = [];
        $newsCategoryInfo = NewsLogic::getNewsCategoryInfo($data['newsInfo']['category_id']);
        if (isset($newsCategoryInfo['code']) && $newsCategoryInfo['code'] == 0 && !empty($newsCategoryInfo['data'])) {
            $data['newsCategoryInfo'] = $newsCategoryInfo['data'];
        }

        // 3: 获取上一个和下一个产品
        $data['nearNewsList'] = [];
        $nearNewsList = NewsLogic::getNearNewsList($newsId);
        if (isset($nearNewsList['code']) && $nearNewsList['code'] == 0 && !empty($nearNewsList['data'])) {
            $data['nearNewsList'] = $nearNewsList['data'];
        }

        // 4: 获取相关产品
        $data['relateNewsList'] = [];
        $relateNewsList = NewsLogic::getRelateNewsList($newsId, $data['newsInfo']['category_id']);
        if (isset($relateNewsList['code']) && $relateNewsList['code'] == 0 && !empty($relateNewsList['data'])) {
            $data['relateNewsList'] = $relateNewsList['data'];
        }

        return view('index.news.news', $data);
    }
}
