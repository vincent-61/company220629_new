<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Log;

class ProductCategoryLogic extends BaseLogic
{
    public function getProductCategoryList($request)
    {
        // 1: 非空校验
        if (empty($request['page']) || empty($request['limit'])) {
            return $this->resMsg(1001, '请求数据有误');
        }

        // 2: 获取数据
        $where = [
            ['product_category.status', '!=', 3]
        ];
        if (!empty($request['category_name'])) {
            $where[] = ['category_name', 'like', '%' . $request['category_name'] . '%'];
        }
        if (!empty($request['status'])) {
            $where[] = ['status', '=', $request['status']];
        }

        $count = ProductCategory::where($where)->count();
        $list = ProductCategory::where($where)->forPage($request['page'], $request['limit'])->orderBy('sort', 'desc')->get();

        // 3: 格式化数据
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$value) {
                $value['created_at'] = !empty($value['created_at']) ? date("Y-m-d H:i:s", $value['created_at']) : '';
                $value['updated_at'] = !empty($value['updated_at']) ? date("Y-m-d H:i:s", $value['updated_at']) : '';
            }
        }

        return ['list' => $list, 'count' => $count];
    }

    public function getProductCategoryInfo($categoryId)
    {
        // 1: 非空判断
        if (empty($categoryId)) {
            return [];
        }

        // 2: 获取信息
        $info = ProductCategory::where(['category_id' => $categoryId])->first();
        if (empty($info)) {
            return [];
        }
        $info = $info->toArray();

        // 3: 获取结果
        return $info;
    }

    public function doEdit($data)
    {
        // 1: 非空判断
        if (empty($data)) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行新增
        try {
            if (!empty($data['category_id'])) {
                $data['updated_at'] = time();
                ProductCategory::where('category_id', $data['category_id'])->update($data);
            } else {
                $data['created_at'] = time();
                ProductCategory::insert($data);
            }
        } catch (\Exception $e) {
            Log::error('[CategoryLogic doEdit] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function setProductCategoryStatus($request)
    {
        // 1: 非空判断
        if (empty($request['category_id']) || empty($request['status'])) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行修改
        try {
            $data = [
                'status' => $request['status'],
                'updated_at' => time(),
            ];
            ProductCategory::where('category_id', $request['category_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[CategoryLogic setStatus] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }
}
