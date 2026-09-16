<?php

namespace App\Http\Logic\Index;

use App\Http\Logic\BaseLogic;
use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\NewsCategory;
use App\Models\Platform;
use App\Models\Qualification;
use App\Models\QualificationCategory;

class IndexLogic extends BaseLogic
{
    public static function getPlatformInfo($platformId)
    {
        // 1: 获取信息
        $info = Platform::where(['platform_id' => $platformId])->first();
        if (empty($info)) {
            return self::resMsgStatic(1001, 'Request fail.');
        }

        // 2: 格式化数据
        $info = $info->toArray();

        return self::resMsgStatic(0, 'Success.', $info);
    }

    public static function getBannerList($type)
    {
        // 1: 获取信息
        $list = Banner::where(['status' => 1, 'type' => $type])->orderBy('sort', 'desc')->limit(5)->get();
        if (empty($list)) {
            return self::resMsgStatic(0, 'Success', []);
        }

        // 2: 格式化数据
        $list = $list->toArray();

        return self::resMsgStatic(0, 'Success.', $list);
    }

}
