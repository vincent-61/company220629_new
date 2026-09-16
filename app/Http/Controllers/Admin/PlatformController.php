<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\PlatformLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PlatformController extends Controller
{

    protected $platformLogic;
    protected $platformId;

    public function __construct(PlatformLogic $platformLogic)
    {
        $this->platformLogic = $platformLogic;
        $this->platformId = 1;
    }

    public function index()
    {
        // 1: 获取信息
        $info = $this->platformLogic->getPlatformInfo($this->platformId);

        // 2: 组装显示数据
        $data['info'] = $info['data'];

        return view('admin.platform.index', $data);
    }

    public function doEdit(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'logo' => 'required',
            'title' => 'required',
            'keywords' => 'required',
            'description' => 'required',
            'main_product' => 'required',
            'about' => 'required',
            'about_img' => 'required',
            'about_video' => '',
            'contact_name' => 'required',
            'telephone' => 'required',
            'phone' => 'required',
            'phone2' => '',
            'qq' => 'required',
            'qq2' => '',
            'email' => 'required',
            'address' => 'required',
            'address2' => '',
            'address_longitude' => 'required',
            'address_latitude' => 'required',
            'address2_longitude' => '',
            'address2_latitude' => '',
            'wechat' => 'required',
            'wechat_img' => 'required',
            'copyright' => 'required',
            'introduce' => 'required',
            'video' => '',
            'video_poster' => '',
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1001, $msg);
        }

        // 2: 获取请求数据
        try {
            $validateData = $validate->validate();
            $validateData['phone2'] = !empty($validateData['phone2']) ? $validateData['phone2'] : '';
            $validateData['qq2'] = !empty($validateData['qq2']) ? $validateData['qq2'] : '';
            $validateData['address2'] = !empty($validateData['address2']) ? $validateData['address2'] : '';
            $validateData['address2_longitude'] = !empty($validateData['address2_longitude']) ? $validateData['address2_longitude'] : '';
            $validateData['address2_latitude'] = !empty($validateData['address2_latitude']) ? $validateData['address2_latitude'] : '';
        } catch (ValidationException $e) {
            return $this->fail(1001, $e->getMessage());
        }

        // 3: 执行修改
        $res = $this->platformLogic->doEdit($this->platformId, $validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }
}
