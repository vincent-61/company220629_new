<?php

namespace App\Http\Logic;

class BaseLogic {

    /**
     * 返回
     *
     * @param int $code
     * @param string $message
     * @param string $data
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-06
     */
    public function resMsg($code = 0, $message = '', $data = []): array
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * 返回
     *
     * @param int $code
     * @param string $message
     * @param string $data
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-06
     */
    public static function resMsgStatic($code = 0, $message = '', $data = []): array
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];
    }

}
