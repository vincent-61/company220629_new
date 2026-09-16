<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\Qualification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualificationLogic extends BaseLogic
{
    public function getQualificationList($request)
    {
        // 1: 非空校验
        if (empty($request['page']) || empty($request['limit'])) {
            return $this->resMsg(1001, '请求数据有误');
        }

        // 2: 获取数据
        $where = [
            ['qualification.status', '!=', 3]
        ];
        if (!empty($request['category_id'])) {
            $where[] = ['qualification.category_id', '=', $request['category_id']];
        }
        if (!empty($request['name'])) {
            $where[] = ['qualification.name', 'like', '%' . $request['name'] . '%'];
        }
        if (!empty($request['status'])) {
            $where[] = ['qualification.status', '=', $request['status']];
        }
        $column = ['qualification.*', 'qualification_category.category_name'];
        $count = Qualification::where($where)->count();
        $list = Qualification::leftJoin('qualification_category', 'qualification.category_id', '=', 'qualification_category.category_id')
            ->where($where)->forPage($request['page'], $request['limit'])->orderBy('qualification.qualification_id', 'desc')->get($column);

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

    public function getQualificationInfo($qualificationId)
    {
        // 1: 非空判断
        if (empty($qualificationId)) {
            return [];
        }

        // 2: 获取信息
        $info = Qualification::leftJoin('qualification_category', 'qualification.category_id', '=', 'qualification_category.category_id')
            ->where(['qualification.qualification_id' => $qualificationId])->first();
        if (empty($info)) {
            return [];
        }
        $info = $info->toArray();
        $info['file_name'] = basename($info['file_src']);

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

            // 2.1: 添加
            $qualificationData = [
                'category_id' => !empty($data['category_id']) ? $data['category_id'] : '',
                'name' => !empty($data['name']) ? $data['name'] : '',
                'img' => !empty($data['img']) ? $data['img'] : '',
                'file_src' => !empty($data['file_src']) ? $data['file_src'] : '',
                'sort' => !empty($data['sort']) ? $data['sort'] : 0,
            ];
            if (!empty($data['qualification_id'])) {
                $qualificationData['updated_at'] = time();
                Qualification::where('qualification_id', $data['qualification_id'])->update($qualificationData);
            } else {
                $qualificationData['created_at'] = time();
                Qualification::insert($qualificationData);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[QualificationLogic doEdit] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function setQualificationStatus($request)
    {
        // 1: 非空判断
        if (empty($request['qualification_id']) || empty($request['status'])) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行修改
        try {
            $data = [
                'status' => $request['status'],
                'updated_at' => time(),
            ];
            Qualification::where('qualification_id', $request['qualification_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[QualificationLogic setStatus] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }
}
