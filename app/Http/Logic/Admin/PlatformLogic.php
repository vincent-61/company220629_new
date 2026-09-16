<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\Platform;
use Illuminate\Support\Facades\Log;

class PlatformLogic extends BaseLogic
{
    /**
     * 获取平台基础信息
     *
     * @param $platformId
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-11-03
     */
    public function getPlatformInfo($platformId)
    {
        // 2: 获取信息
        $info = Platform::where(['platform_id' => $platformId])->first();
        if (empty($info)) {
            return $this->resMsg(1001, '查询数据有误');
        }

        return $this->resMsg(0, '成功', $info->toArray());
    }

    /**
     * 修改个人资料
     *
     * @param $platformId
     * @param $data
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-10
     */
    public function doEdit($platformId, $data)
    {
        // 1: 非空判断
        if (empty($platformId) || empty($data)) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 修改密码
        try {
            Platform::where('platform_id', $platformId)->update($data);
        } catch (\Exception $e) {
            Log::error('[PlatformLogic doEdit] message: ' . $e->getMessage());
            return $this->resMsg(1002, '修改失败');
        }

        return $this->resMsg(0, '修改成功');
    }


}
