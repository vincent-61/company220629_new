<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductLogic extends BaseLogic
{
    public function getProductList($request)
    {
        // 1: 非空校验
        if (empty($request['page']) || empty($request['limit'])) {
            return $this->resMsg(1001, '请求数据有误');
        }

        // 2: 获取数据
        $where = [
            ['product.status', '!=', 3]
        ];
        if (!empty($request['category_id'])) {
            $where[] = ['product.category_id', '=', $request['category_id']];
        }
        if (!empty($request['product_name'])) {
            $where[] = ['product.product_name', 'like', '%' . $request['product_name'] . '%'];
        }
        if (!empty($request['status'])) {
            $where[] = ['product.status', '=', $request['status']];
        }
        $column = ['product.*', 'product_category.category_name'];
        $count = Product::where($where)->count();
        $list = Product::leftJoin('product_category', 'product.category_id', '=', 'product_category.category_id')
            ->where($where)->forPage($request['page'], $request['limit'])->orderBy('product.product_id', 'desc')->get($column);

        // 3: 格式化数据
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$value) {
                $value['img_190'] = '';
                if (!empty($value['img'])) {
                    $pathInfo = pathinfo($value['img']);
                    $value['img_190'] = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_190' . '.' . $pathInfo['extension'];
                }

                $value['created_at'] = !empty($value['created_at']) ? date("Y-m-d H:i:s", $value['created_at']) : '';
                $value['updated_at'] = !empty($value['updated_at']) ? date("Y-m-d H:i:s", $value['updated_at']) : '';
            }
        }

        return ['list' => $list, 'count' => $count];
    }

    public function getProductInfo($productId)
    {
        // 1: 非空判断
        if (empty($productId)) {
            return [];
        }

        // 2: 获取信息
        $column = ['product.*', 'product_category.category_name'];
        $info = Product::leftJoin('product_category', 'product.category_id', '=', 'product_category.category_id')
            ->where(['product_id' => $productId])->first($column);
        if (empty($info)) {
            return [];
        }
        $info = $info->toArray();

        // 3: 返回结果
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
            DB::beginTransaction();

            // 2.1: 添加产品
            $productData = [
                'category_id' => !empty($data['category_id']) ? $data['category_id'] : 0,
                'product_name' => !empty($data['product_name']) ? $data['product_name'] : '',
                'img' => !empty($data['img']) ? $data['img'] : '',
                'desc' => !empty($data['desc']) ? $data['desc'] : '',
                'content' => !empty($data['content']) ? $data['content'] : 0,
                'sort' => !empty($data['sort']) ? $data['sort'] : 0,
                'recommend' => !empty($data['recommend']) ? $data['recommend'] : 1,
            ];
            if (!empty($data['product_id'])) {
                $productData['updated_at'] = time();
                Product::where('product_id', $data['product_id'])->update($productData);
            } else {
                $productData['created_at'] = time();
                Product::insert($productData);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ProductLogic doEdit] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function setProductStatus($request)
    {
        // 1: 非空判断
        if (empty($request['product_id']) || empty($request['status'])) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行修改
        try {
            $data = [
                'status' => $request['status'],
                'updated_at' => time(),
            ];
            Product::where('product_id', $request['product_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[ProductLogic setProductStatus] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function setProductRecommend($request)
    {
        // 1: 非空判断
        if (empty($request['product_id']) || empty($request['recommend'])) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行修改
        try {
            $data = [
                'recommend' => $request['recommend'],
                'updated_at' => time(),
            ];
            Product::where('product_id', $request['product_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[ProductLogic setProductRecommend] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function doDelBatch($request)
    {
        // 1: 非空判断
        if (empty($request['data'])) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 数据判断
        $batchData = json_decode($request['data'], true);
        if (empty($batchData) || !is_array($batchData)) {
            return $this->resMsg(1002, '请求参数有误');
        }

        // 3: 执行修改
        try {
            DB::beginTransaction();
            foreach ($batchData as $batch) {
                // 执行删除
                $data['status'] = 3;
                $data['updated_at'] = time();
                Product::where(['product_id' => $batch])->update($data);
            }
            DB::commit();
        } catch (\Exception $e) {
            Log::error('[ProductLogic doDelBatch] message: ' . $e->getMessage());
            return $this->resMsg(1003, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }
}
