<?php

namespace App\Http\Logic\Index;

use App\Http\Logic\BaseLogic;
use App\Models\News;
use App\Models\NewsCategory;

class NewsLogic extends BaseLogic
{
    public static function getNewsCategoryList()
    {
        // 1: 获取信息
        $list = NewsCategory::where(['status' => 1])->orderBy('sort', 'desc')->get();
        if (empty($list)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        $list = $list->toArray();
        $list = array_column($list, null, 'category_id');

        return self::resMsgStatic(0, 'Success.', $list);
    }

    public static function getNewsListByCategoryName($categoryName)
    {
        // 1: 获取新闻栏目列表
        $newsCategoryInfo = NewsCategory::where(['category_name' => $categoryName, 'status' => 1])->orderBy('sort', 'desc')->first();
        if (empty($newsCategoryInfo)) {
            return self::resMsgStatic(0, 'Success', []);
        }
        $news['newsCategoryInfo'] = $newsCategoryInfo->toArray();

        // 2: 获取新闻列表
        $news['newsList'] = [];
        $newsList = NewsLogic::getNewsList($newsCategoryInfo['category_id'], 1, 8);
        if (isset($newsList['code']) && $newsList['code'] == 0 && !empty($newsList['data']['newsList'])) {
            $news['newsList'] = $newsList['data']['newsList'];
        }

        return self::resMsgStatic(0, 'Success.', $news);
    }

    public static function getNewsCategoryInfo($categoryId)
    {
        // 1: 获取信息
        $info = NewsCategory::where(['category_id' => $categoryId, 'status' => 1,])->orderBy('sort', 'desc')->first();
        if (empty($info)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 2: 格式化数据
        $info = $info->toArray();

        return self::resMsgStatic(0, 'Success.', $info);
    }

    public static function getNewsList($categoryId, $page = 1, $perPage = 15)
    {
        // 1: 组装查询条件
        $where = [
            ['status', '=', 1]
        ];
        if (!empty($categoryId)) {
            $where[] = ['category_id', '=', $categoryId];
        }

        // 2: 查询信息
        $count = News::where($where)->count();
        $list = News::where($where)->orderBy('sort', 'desc')->orderBy('news_id', 'desc')->forPage($page, $perPage)->get();
        if (empty($list)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 3: 格式化数据
        $list = $list->toArray();
        foreach ($list as &$value) {
            $value['news_time'] = !empty($value['news_time']) ? date("Y-m-d", $value['news_time']) : '';
            $value['content'] = !empty($value['content']) ? removeHtml($value['content'], 90) . '...' : '';

            $value['img_160'] = '';
            if (!empty($value['img'])) {
                $pathInfo = pathinfo($value['img']);
                $value['img_160'] = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_160' . '.' . $pathInfo['extension'];
            }

            $value['title_format'] = $value['title'];
            if (!empty($value['title']) && mb_strlen($value['title']) > 24) {
                $value['title_format'] = mb_substr(strip_tags($value['title']), 0, 24) . '...';
            }
        }

        // 4: 组装数据
        $data = [
            'pageCount' => ceil($count / $perPage),
            'newsList' => $list,
        ];

        return self::resMsgStatic(0, 'Success.', $data);
    }

    public static function getNewsDetail($newsId)
    {
        // 1: 非空判断
        if (empty($newsId)) {
            return self::resMsgStatic(1001, 'Request fail.');
        }

        // 2: 组装查询条件
        $where = [
            ['status', '=', 1],
            ['news_id', '=', $newsId],
        ];

        // 3: 查询信息
        $info = News::where($where)->first();
        if (empty($info)) {
            return self::resMsgStatic(1002, 'Request fail.');
        }
        $info = $info->toArray();
        $info['news_time'] = !empty($info['news_time']) ? date("Y-m-d", $info['news_time']) : '';

        return self::resMsgStatic(0, 'Success.', $info);
    }

    public static function getNearNewsList($newsId)
    {
        // 1: 非空判断
        if (empty($newsId)) {
            return self::resMsgStatic(1001, 'Request fail.');
        }

        // 2: 获取上一个和下一个产品
        $beforeInfo = News::where([['status', '=', 1], ['news_id', '<', $newsId]])->orderBy('sort', 'desc')->orderBy('news_id', 'desc')->first();
        $afterInfo = News::where([['status', '=', 1], ['news_id', '>', $newsId]])->orderBy('sort', 'desc')->orderBy('news_id', 'desc')->first();

        // 3: 查询信息
        $data['before'] = $data['after'] = [];
        if (!empty($beforeInfo)) {
            $data['before'] = $beforeInfo->toArray();
            $data['before']['titleFormat'] = !empty($data['before']['title']) ? mb_substr(strip_tags($data['before']['title']), 0, 15) . '...' : '';
        }
        if (!empty($afterInfo)) {
            $data['after'] = $afterInfo->toArray();
            $data['after']['titleFormat'] = !empty($data['after']['title']) ? mb_substr(strip_tags($data['after']['title']), 0, 15) . '...' : '';
        }

        return self::resMsgStatic(0, 'Success.', $data);
    }

    public static function getRelateNewsList($newsId, $categoryId)
    {
        // 1: 查询当前栏目下是否大于4个产品，大于等于4个结束，否则继续查询
        $idList = [$newsId];
        $list = News::where(['status' => 1, 'category_id' => $categoryId])->whereNotIn('news_id', $idList)
            ->orderBy('sort', 'desc')->orderBy('news_id', 'desc')->limit(4)->get();
        $count = 0;
        if (!empty($list)) {
            $list = $list->toArray();
            $idList1 = array_column($list, 'news_id', '');
            $idList = array_merge($idList, $idList1);
            $count = count($list);
        }
        if ($count >= 4) {
            return self::resMsgStatic(0, 'Success.', $list);
        }

        // 2: 查询所有产品，获取所需要的剩下产品
        $needNum1 = 4 - $count;
        $list2 = News::where(['status' => 1])->whereNotIn('news_id', $idList)->orderBy('sort', 'desc')->orderBy('news_id', 'desc')->limit($needNum1)->get();
        if (!empty($list2)) {
            $list2 = $list2->toArray();
            $list = array_merge($list, $list2);
        }

        // 3: 格式化所有参数
        if (!empty($list)) {
            foreach ($list as &$value) {
                $value['titleFormat'] = !empty($value['title']) ? mb_substr(strip_tags($value['title']), 0, 15) . '...' : '';
                $value['news_time'] = !empty($value['news_time']) ? date("Y-m-d", $value['news_time']) : '';
            }
        }
        return self::resMsgStatic(0, 'Success.', $list);
    }
}
