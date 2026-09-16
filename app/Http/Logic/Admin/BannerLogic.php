<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\Banner;
use App\Models\Platform;
use Illuminate\Support\Facades\Log;

class BannerLogic extends BaseLogic
{
    public function getBannerList($request)
    {
        // 1: 非空校验
        if (empty($request['page']) || empty($request['limit'])) {
            return $this->resMsg(1001, '请求数据有误');
        }

        // 2: 获取数据
        $where = [
            ['status', '!=', 3]
        ];
        if (!empty($request['remark'])) {
            $where[] = ['remark', 'like', '%' . $request['remark'] . '%'];
        }
        if (!empty($request['status'])) {
            $where[] = ['status', '=', $request['status']];
        }
        $count = Banner::where($where)->count();
        $list = Banner::where($where)->forPage($request['page'], $request['limit'])->orderBy('status', 'asc')->orderBy('sort', 'desc')->get();

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

    public function getBannerInfo($bannerId)
    {
        // 1: 非空判断
        if (empty($bannerId)) {
            return [];
        }

        // 2: 获取信息
        $info = Banner::where(['banner_id' => $bannerId])->first();
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
            $data['remark'] = !empty($data['remark']) ? $data['remark'] : '';
            $data['url'] = !empty($data['url']) ? $data['url'] : '';
            if (!empty($data['banner_id'])) {
                $data['updated_at'] = time();
                Banner::where('banner_id', $data['banner_id'])->update($data);
            } else {
                $data['created_at'] = time();
                Banner::insert($data);
            }
        } catch (\Exception $e) {
            Log::error('[BannerLogic doEdit] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function setBannerStatus($request)
    {
        // 1: 非空判断
        if (empty($request['banner_id']) || empty($request['status'])) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行修改
        try {
            $data = [
                'status' => $request['status'],
                'updated_at' => time(),
            ];
            Banner::where('banner_id', $request['banner_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[BannerLogic setStatus] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }
}
