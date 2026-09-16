<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\NewsCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsCategoryLogic extends BaseLogic
{
    public function getNewsCategoryList($request)
    {
        // 1: 非空校验
        if (empty($request['page']) || empty($request['limit'])) {
            return $this->resMsg(1001, '请求数据有误');
        }

        // 2: 获取数据
        $where = [
            ['status', '!=', 3]
        ];
        if (!empty($request['category_name'])) {
            $where[] = ['category_name', 'like', '%' . $request['category_name'] . '%'];
        }
        if (!empty($request['status'])) {
            $where[] = ['status', '=', $request['status']];
        }
        $count = NewsCategory::where($where)->count();
        $list = NewsCategory::where($where)->forPage($request['page'], $request['limit'])->orderBy('category_id', 'desc')->get();

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

    public function getNewsCategoryInfo($newsId)
    {
        // 1: 非空判断
        if (empty($newsId)) {
            return [];
        }

        // 2: 获取信息
        $info = NewsCategory::where(['category_id' => $newsId])->first();
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

            // 2.1: 添加新闻
            $newsData = [
                'category_name' => !empty($data['category_name']) ? $data['category_name'] : '',
                'sort' => !empty($data['sort']) ? $data['sort'] : 0,
            ];
            if (!empty($data['category_id'])) {
                $newsData['updated_at'] = time();
                NewsCategory::where('category_id', $data['category_id'])->update($newsData);
            } else {
                $newsData['created_at'] = time();
                NewsCategory::insert($newsData);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[NewsCategoryLogic doEdit] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function setNewsCategoryStatus($request)
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
            NewsCategory::where('category_id', $request['category_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[NewsCategoryLogic setStatus] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }
}
