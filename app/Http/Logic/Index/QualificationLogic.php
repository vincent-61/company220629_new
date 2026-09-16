<?php

namespace App\Http\Logic\Index;

use App\Http\Logic\BaseLogic;
use App\Models\Qualification;
use App\Models\QualificationCategory;

class QualificationLogic extends BaseLogic
{
    public static function getQualificationCategoryList()
    {
        // 1: 获取信息
        $list = QualificationCategory::where(['status' => 1])->orderBy('sort', 'desc')->get();
        if (empty($list)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        $list = $list->toArray();
        $list = array_column($list, null, 'category_id');

        return self::resMsgStatic(0, 'Success.', $list);
    }

    public static function getQualificationListByCategoryName($categoryName)
    {
        // 1: 获取资质栏目列表
        $qualificationCategoryInfo = QualificationCategory::where(['category_name' => $categoryName, 'status' => 1])->orderBy('sort', 'desc')->first();
        if (empty($qualificationCategoryInfo)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 2: 获取资质列表
        $qualificationList = [];
        $list = QualificationLogic::getQualificationList($qualificationCategoryInfo['category_id'], 1, 40);
        if (isset($list['code']) && $list['code'] == 0 && !empty($list['data']['qualificationList'])) {
            $qualificationList = $list['data']['qualificationList'];
        }

        return self::resMsgStatic(0, 'Success.', $qualificationList);
    }

    public static function getQualificationCategoryInfo($categoryId)
    {
        // 1: 组装查询条件
        $where = [
            ['status', '=', 1]
        ];
        if (!empty($categoryId)) {
            $where[] = ['category_id', '=', $categoryId];
        }

        // 2: 获取信息
        $info = QualificationCategory::where($where)->orderBy('sort', 'desc')->first();
        if (empty($info)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 3: 格式化数据
        $info = $info->toArray();

        return self::resMsgStatic(0, 'Success.', $info);
    }

    public static function getQualificationList($categoryId, $page = 1, $perPage = 15)
    {
        // 1: 组装查询条件
        $where = [
            ['status', '=', 1]
        ];
        if (!empty($categoryId)) {
            $where[] = ['category_id', '=', $categoryId];
        }

        // 2: 查询信息
        $count = Qualification::where($where)->count();
        $list = Qualification::where($where)->orderBy('sort', 'desc')->orderBy('qualification_id', 'desc')->forPage($page, $perPage)->get();
        if (empty($list)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 3: 格式化数据
        foreach ($list as &$value) {
            $value['img_190'] = '';
            if (!empty($value['img'])) {
                $pathInfo = pathinfo($value['img']);
                $value['img_190'] = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_190' . '.' . $pathInfo['extension'];
                $value['img_55'] = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_55' . '.' . $pathInfo['extension'];
            }
        }

        // 4: 组装数据
        $data = [
            'pageCount' => ceil($count / $perPage),
            'qualificationList' => $list->toArray(),
        ];

        return self::resMsgStatic(0, 'Success.', $data);
    }

    public static function getQualificationDetail($qualificationId)
    {
        // 1: 非空判断
        if (empty($qualificationId)) {
            return self::resMsgStatic(1001, 'Request fail.');
        }

        // 2: 组装查询条件
        $where = [
            ['status', '=', 1],
            ['qualification_id', '=', $qualificationId],
        ];

        // 3: 查询信息
        $info = Qualification::where($where)->first();
        if (empty($info)) {
            return self::resMsgStatic(1002, 'Request fail.');
        }
        $info = $info->toArray();

        return self::resMsgStatic(0, 'Success.', $info);
    }

    public static function getNearQualificationList($qualificationId)
    {
        // 1: 非空判断
        if (empty($qualificationId)) {
            return self::resMsgStatic(1001, 'Request fail.');
        }

        // 2: 获取上一个和下一个产品
        $beforeInfo = Qualification::where([['status', '=', 1], ['qualification_id', '<', $qualificationId]])->orderBy('sort', 'desc')->orderBy('qualification_id', 'desc')->first();
        $afterInfo = Qualification::where([['status', '=', 1], ['qualification_id', '>', $qualificationId]])->orderBy('sort', 'desc')->orderBy('qualification_id', 'desc')->first();

        // 3: 查询信息
        $data['before'] = $data['after'] = [];
        if (!empty($beforeInfo)) {
            $data['before'] = $beforeInfo->toArray();
        }
        if (!empty($afterInfo)) {
            $data['after'] = $afterInfo->toArray();
        }

        return self::resMsgStatic(0, 'Success.', $data);
    }

    public static function getRelateQualificationList($qualificationId, $categoryId)
    {
        // 1: 查询当前栏目下是否大于4个产品，大于等于4个结束，否则继续查询
        $idList = [$qualificationId];
        $list = Qualification::where(['status' => 1, 'category_id' => $categoryId])->whereNotIn('qualification_id', $idList)
            ->orderBy('sort', 'desc')->orderBy('qualification_id', 'desc')->limit(4)->get();
        $count = 0;
        if (!empty($list)) {
            $list = $list->toArray();
            $idList1 = array_column($list, 'qualification_id', '');
            $idList = array_merge($idList, $idList1);
            $count = count($list);
        }
        if ($count >= 4) {
            return self::resMsgStatic(0, 'Success.', $list);
        }

        // 2: 查询所有产品，获取所需要的剩下产品
        $needNum1 = 4 - $count;
        $list2 = Qualification::where(['status' => 1])->whereNotIn('qualification_id', $idList)->orderBy('sort', 'desc')->orderBy('qualification_id', 'desc')->limit($needNum1)->get();
        if (!empty($list2)) {
            $list2 = $list2->toArray();
            $list = array_merge($list, $list2);
        }

        return self::resMsgStatic(0, 'Success.', $list);
    }
}
