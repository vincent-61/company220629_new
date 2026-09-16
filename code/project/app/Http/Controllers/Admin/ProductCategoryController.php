<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\ProductCategoryLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductCategoryController extends Controller
{

    protected $productCategoryLogic;

    public function __construct(ProductCategoryLogic $productCategoryLogic)
    {
        $this->productCategoryLogic = $productCategoryLogic;
    }

    public function index()
    {
        return view('admin.product_category.index');
    }

    public function getProductCategoryList(Request $request)
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
        $list = $this->productCategoryLogic->getProductCategoryList($validateData);
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
        $categoryId = $request->get('category_id');
        $info = [];
        if (!empty($categoryId)) {
            $info = $this->productCategoryLogic->getProductCategoryInfo($categoryId);
        }

        // 2: 组装显示数据
        $data['categoryInfo'] = $info;

        return view('admin.product_category.edit', $data);
    }

    public function doEdit(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'category_id' => '',
            'category_name' => 'required',
            'img' => 'required',
            'sort' => '',
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
        $res = $this->productCategoryLogic->doEdit($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }

    public function setProductCategoryStatus(Request $request)
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
        $res = $this->productCategoryLogic->setProductCategoryStatus($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }
}
