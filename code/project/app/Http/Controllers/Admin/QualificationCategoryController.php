<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\QualificationCategoryLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class QualificationCategoryController extends Controller
{

    protected $qualificationCategoryLogic;

    public function __construct(QualificationCategoryLogic $qualificationCategoryLogic)
    {
        $this->qualificationCategoryLogic = $qualificationCategoryLogic;
    }

    public function index()
    {
        return view('admin.qualification_category.index');
    }

    public function getQualificationCategoryList(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'page' => '',
            'limit' => '',
            'category_name' => '',
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
        $list = $this->qualificationCategoryLogic->getQualificationCategoryList($validateData);
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
        $qualificationId = $request->get('category_id');
        $info = [];
        if (!empty($qualificationId)) {
            $info = $this->qualificationCategoryLogic->getQualificationCategoryInfo($qualificationId);
        }

        // 3: 组装显示数据
        $data['categoryInfo'] = $info;

        return view('admin.qualification_category.edit', $data);
    }

    public function doEdit(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'category_id' => '',
            'category_name' => 'required',
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
        $res = $this->qualificationCategoryLogic->doEdit($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }

    public function setQualificationCategoryStatus(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'category_id' => 'required',
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
        $res = $this->qualificationCategoryLogic->setQualificationCategoryStatus($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }
}
