<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 返回成功
     *
     * @param $message
     * @param array $data
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-03
     */
    public function success($message, $data = [])
    {
        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * 返回失败
     *
     * @param $code
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-03
     */
    public function fail($code, $message = '')
    {
        return response()->json([
            'code' => $code,
            'message' => $message
        ]);
    }

    /**
     * 上传成功
     *
     * @param $message
     * @param array $data
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-11-04
     */
    public function uploadSuccess($message, $data = [])
    {
        return response()->json([
            'code' => 0,
            'msg' => $message,
            'data' => $data
        ]);
    }

    /**
     * 上传失败
     *
     * @param $code
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-11-04
     */
    public function uploadFail($code, $message = '')
    {
        return response()->json([
            'code' => $code,
            'msg' => $message,
            'data' => []
        ]);
    }
}
