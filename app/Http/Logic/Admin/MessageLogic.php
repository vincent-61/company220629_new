<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageLogic extends BaseLogic
{
    public function getMessageList($request)
    {
        // 1: 非空校验
        if (empty($request['page']) || empty($request['limit'])) {
            return $this->resMsg(1001, '请求数据有误');
        }

        // 2: 获取数据
        $where = [
            ['status', '!=', 2]
        ];
        if (!empty($request['title'])) {
            $where[] = ['name', 'like', '%' . $request['name'] . '%'];
        }
        if (!empty($request['phone'])) {
            $where[] = ['phone', '=', $request['phone']];
        }
        $count = Message::where($where)->count();
        $list = Message::where($where)->forPage($request['page'], $request['limit'])->orderBy('message_id', 'desc')->get();

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

    public function getMessageInfo($messageId)
    {
        // 1: 非空判断
        if (empty($messageId)) {
            return [];
        }

        // 2: 获取信息
        $info = Message::where(['message_id' => $messageId])->first();
        if (empty($info)) {
            return [];
        }
        $info = $info->toArray();

        // 3: 返回结果
        return $info;
    }

    public function setMessageStatus($request)
    {
        // 1: 非空判断
        if (empty($request['message_id']) || empty($request['status'])) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行修改
        try {
            $data = [
                'status' => $request['status'],
                'updated_at' => time(),
            ];
            Message::where('message_id', $request['message_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[MessageLogic setStatus] message: ' . $e->getMessage());
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
                $data['status'] = 2;
                $data['updated_at'] = time();
                Message::where(['message_id' => $batch])->update($data);
            }
            DB::commit();
        } catch (\Exception $e) {
            Log::error('[MessageLogic doDelBatch] message: ' . $e->getMessage());
            return $this->resMsg(1003, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }
}
