<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\QualificationLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class QualificationController extends Controller
{

    protected $qualificationLogic;

    public function __construct(QualificationLogic $qualificationLogic)
    {
        $this->qualificationLogic = $qualificationLogic;
    }

    public function index()
    {
        return view('admin.qualification.index');
    }

    public function getQualificationList(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'page' => '',
            'limit' => '',
            'category_id' => '',
            'name' => '',
            'status' => '',
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
        $list = $this->qualificationLogic->getQualificationList($validateData);
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
        $qualificationId = $request->get('qualification_id');
        $info = [];
        if (!empty($qualificationId)) {
            $info = $this->qualificationLogic->getQualificationInfo($qualificationId);
        }

        // 2: 组装显示数据
        $data['qualificationInfo'] = $info;

        return view('admin.qualification.edit', $data);
    }

    public function doEdit(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'qualification_id' => '',
            'category_id' => 'required',
            'name' => 'required',
            'img' => 'required',
            'file_src' => '',
            'sort' => 'required',
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
        $res = $this->qualificationLogic->doEdit($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }

    public function setQualificationStatus(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'qualification_id' => 'required',
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
        $res = $this->qualificationLogic->setQualificationStatus($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }
}
