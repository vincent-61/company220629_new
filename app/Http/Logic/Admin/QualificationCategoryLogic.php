<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\QualificationCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualificationCategoryLogic extends BaseLogic
{
    public function getQualificationCategoryList($request)
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
        $count = QualificationCategory::where($where)->count();
        $list = QualificationCategory::where($where)->forPage($request['page'], $request['limit'])->orderBy('category_id', 'desc')->get();

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

    public function getQualificationCategoryInfo($qualificationId)
    {
        // 1: 非空判断
        if (empty($qualificationId)) {
            return [];
        }

        // 2: 获取信息
        $info = QualificationCategory::where(['category_id' => $qualificationId])->first();
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

            // 2.1: 添加资质
            $qualificationData = [
                'category_name' => !empty($data['category_name']) ? $data['category_name'] : '',
                'sort' => !empty($data['sort']) ? $data['sort'] : 0,
            ];
            if (!empty($data['category_id'])) {
                $qualificationData['updated_at'] = time();
                QualificationCategory::where('category_id', $data['category_id'])->update($qualificationData);
            } else {
                $qualificationData['created_at'] = time();
                QualificationCategory::insert($qualificationData);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[QualificationCategoryLogic doEdit] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function setQualificationCategoryStatus($request)
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
            QualificationCategory::where('category_id', $request['category_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[QualificationCategoryLogic setStatus] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }
}
