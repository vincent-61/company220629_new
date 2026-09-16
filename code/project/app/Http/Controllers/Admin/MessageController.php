<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\MessageLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    protected $messageLogic;

    public function __construct(MessageLogic $messageLogic)
    {
        $this->messageLogic = $messageLogic;
    }

    public function index()
    {
        return view('admin.message.index');
    }

    public function getMessageList(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'page' => '',
            'limit' => '',
            'name' => '',
            'phone' => '',
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1001, $msg);
        }

        // 2: 获取请求数据
        try {
            $validateData = $validate->validate();
            $validateData['page'] = !empty($validateData['page']) ? $validateData['page'] : 1;
            $validateData['limit'] = !empty($validateData['limit']) ? $validateData['limit'] : 15;

        } catch (ValidationException $e) {
            return $this->fail(1001, $e->getMessage());
        }

        // 3: 获取数据
        $list = $this->messageLogic->getMessageList($validateData);
        return response()->json([
            'code' => 0,
            'msg' => '查询成功',
            'count' => $list['count'],
            'data' => $list['list']
        ]);

    }

    public function edit(Request $request)
    {
        // 1: 获取信息
        $messageId = $request->get('message_id');
        $info = [];
        if (!empty($messageId)) {
            $info = $this->messageLogic->getMessageInfo($messageId);
        }

        // 3: 组装显示数据
        $data['messageInfo'] = $info;

        return view('admin.message.edit', $data);
    }

    public function setMessageStatus(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'message_id' => 'required',
            'status' => 'required'
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1001, $msg);
        }

        // 2: 获取请求数据
        try {
            $validateData = $validate->validate();
        } catch (ValidationException $e) {
            return $this->fail(1001, $e->getMessage());
        }

        // 3: 修改数据
        $res = $this->messageLogic->setMessageStatus($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }

    public function doDelBatch(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'data' => 'required',
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1001, $msg);
        }

        // 2: 获取请求数据
        try {
            $validateData = $validate->validate();
        } catch (ValidationException $e) {
            return $this->fail(1001, $e->getMessage());
        }

        // 3: 执行修改
        $res = $this->messageLogic->doDelBatch($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功', $res['data']);
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }
}
