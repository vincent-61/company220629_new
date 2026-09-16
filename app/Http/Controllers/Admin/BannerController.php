<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\BannerLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{

    protected $bannerLogic;

    public function __construct(BannerLogic $bannerLogic)
    {
        $this->bannerLogic = $bannerLogic;
    }

    public function index()
    {
        return view('admin.banner.index');
    }

    public function getBannerList(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'page' => '',
            'limit' => '',
            'remark' => '',
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
        $list = $this->bannerLogic->getBannerList($validateData);
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
        $bannerId = $request->get('banner_id');
        $info = [];
        if (!empty($bannerId)) {
            $info = $this->bannerLogic->getBannerInfo($bannerId);
        }

        // 2: 组装显示数据
        $data['bannerInfo'] = $info;

        return view('admin.banner.edit', $data);
    }

    public function doEdit(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'banner_id' => '',
            'img' => 'required',
            'remark' => '',
            'sort' => '',
            'url' => '',
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
        $res = $this->bannerLogic->doEdit($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }

    public function setBannerStatus(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'banner_id' => 'required',
            'status' => 'required'
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1001, $msg);
        }

        // 2: 获取请求数据
        try {
            $validateData = $validate->validate();
            $validateData['client_ip'] = $request->getClientIp();
        } catch (ValidationException $e) {
            return $this->fail(1001, $e->getMessage());
        }

        // 3: 修改数据
        $res = $this->bannerLogic->setBannerStatus($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }
}
