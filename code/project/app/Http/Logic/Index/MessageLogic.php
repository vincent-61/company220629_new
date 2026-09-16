<?php

namespace App\Http\Logic\Index;

use App\Http\Logic\BaseLogic;
use App\Models\Contact;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class MessageLogic extends BaseLogic
{
    public static function sendMessage($request)
    {
        // 1: 非空判断
        if (empty($request) || empty($request['ip'])) {
            return self::resMsgStatic(1001, '请求参数有误');
        }

        // 2: 查询留言是否存在
        $where = [
            ['ip', '=', $request['ip']],
            ['status', '=', 1],
            ['created_at', '>', strtotime('-5 minutes')]
        ];
        $messageCount = Message::where($where)->count();
        if ($messageCount >= 5) {
            return self::resMsgStatic(1002, '请求过于频繁，请等5分钟再试');
        }

        // 2: 提交留言
        try {
            $messageData = [
                'name' => $request['name'],
                'phone' => $request['phone'],
                'email' => $request['email'],
                'content' => $request['content'],
                'ip' => $request['ip'],
                'status' => 1,
                'created_at' => time(),
            ];
            Message::insert($messageData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return self::resMsgStatic(1003, '提交失败');
        }
        return self::resMsgStatic(0, '提交成功');
    }
}
