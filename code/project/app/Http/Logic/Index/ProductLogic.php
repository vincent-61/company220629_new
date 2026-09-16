<?php

namespace App\Http\Logic\Index;

use App\Http\Logic\BaseLogic;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductImg;

class ProductLogic extends BaseLogic
{
    public static function getProductCategoryList()
    {
        // 1: 获取信息
        $list = ProductCategory::where(['status' => 1, 'parent_id' => 0])->orderBy('sort', 'desc')->get();
        if (empty($list)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 2: 格式化数据
        $list = $list->toArray();
        foreach ($list as &$value) {
            $value['img_50'] = '';
            if (!empty($value['img'])) {
                $pathInfo = pathinfo($value['img']);
                $value['img_50'] = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_50' . '.' . $pathInfo['extension'];
            }
        }

        return self::resMsgStatic(0, 'Success.', $list);
    }

    public static function getRecommendProductList()
    {
        // 1: 获取信息
        $list = Product::where(['recommend' => 2, 'status' => 1])->orderBy('sort', 'desc')->get();
        if (empty($list)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 2: 格式化数据
        $list = $list->toArray();
        foreach ($list as &$value) {
            $value['img_190'] = '';
            if (!empty($value['img'])) {
                $pathInfo = pathinfo($value['img']);
                $value['img_190'] = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_190' . '.' . $pathInfo['extension'];
            }
        }

        return self::resMsgStatic(0, 'Success.', $list);
    }

    public static function getProductCategoryInfo($categoryId)
    {
        // 1: 获取信息
        $info = ProductCategory::where(['category_id' => $categoryId, 'status' => 1, 'parent_id' => 0])->orderBy('sort', 'desc')->first();
        if (empty($info)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 2: 格式化数据
        $info = $info->toArray();

        return self::resMsgStatic(0, 'Success.', $info);
    }

    public static function getProductList($categoryId = 0, $page = 1, $perPage = 12)
    {
        // 1: 组装查询条件
        $search = Product::where(['status' => 1]);
        if (!empty($categoryId)) {
            $search = $search->where(['category_id' => $categoryId]);
        }

        // 2: 查询信息
        $count = $search->count();
        $list = $search->orderBy('sort', 'desc')->orderBy('product_id', 'desc')->forPage($page, $perPage)->get();
        if (empty($list)) {
            return self::resMsgStatic(1001, 'Request fail.');
        }

        // 3: 格式化数据
        $list = $list->toArray();
        foreach ($list as &$value) {
            $value['img_190'] = '';
            if (!empty($value['img'])) {
                $pathInfo = pathinfo($value['img']);
                $value['img_190'] = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_190' . '.' . $pathInfo['extension'];
            }
        }

        // 4: 组装数据
        $data = [
            'pageCount' => ceil($count / $perPage),
            'productList' =>  $list,
        ];

        return self::resMsgStatic(0, 'Success.', $data);
    }

    public static function getProductDetail($productId)
    {
        // 1: 非空判断
        if (empty($productId)) {
            return self::resMsgStatic(1001, 'Request fail.');
        }

        // 2: 组装查询条件
        $where = [
            ['status', '=', 1],
            ['product_id', '=', $productId],
        ];

        // 3: 查询信息
        $info = Product::where($where)->first();
        if (empty($info)) {
            return self::resMsgStatic(1002, 'Request fail.');
        }
        $info = $info->toArray();

        return self::resMsgStatic(0, 'Success.', $info);
    }

    public static function getNearProductList($productId)
    {
        // 1: 非空判断
        if (empty($productId)) {
            return self::resMsgStatic(1001, 'Request fail.');
        }

        // 2: 获取上一个和下一个产品
        $beforeInfo = Product::where([['status', '=', 1], ['product_id', '<', $productId]])->orderBy('sort', 'desc')->orderBy('product_id', 'desc')->first();
        $afterInfo = Product::where([['status', '=', 1], ['product_id', '>', $productId]])->orderBy('sort', 'desc')->orderBy('product_id', 'desc')->first();

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

    public static function getRelateProductList($productId, $categoryId)
    {
        // 1: 查询当前栏目下是否大于4个产品，大于等于4个结束，否则继续查询
        $idList = [$productId];
        $list = Product::where(['status' => 1, 'category_id' => $categoryId])->whereNotIn('product_id', $idList)
            ->orderBy('sort', 'desc')->orderBy('product_id', 'desc')->limit(4)->get();
        $count = 0;
        if (!empty($list)) {
            $list = $list->toArray();
            $idList1 = array_column($list, 'product_id', '');
            $idList = array_merge($idList, $idList1);
            $count = count($list);
        }
        if ($count >= 4) {
            return self::resMsgStatic(0, 'Success.', $list);
        }

        // 2: 查询所有产品，获取所需要的剩下产品
        $needNum1 = 4 - $count;
        $list2 = Product::where(['status' => 1])->whereNotIn('product_id', $idList)->orderBy('recommend', 'desc')->orderBy('sort', 'desc')->orderBy('product_id', 'desc')->limit($needNum1)->get();
        if (!empty($list2)) {
            $list2 = $list2->toArray();
            $list = array_merge($list, $list2);
        }

        return self::resMsgStatic(0, 'Success.', $list);
    }

}
