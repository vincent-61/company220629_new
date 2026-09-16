<?php

namespace App\Http\Controllers\Index;

use App\Common\Captcha;
use App\Http\Controllers\Controller;
use App\Http\Logic\Index\IndexLogic;
use App\Http\Logic\Index\MessageLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{

    public function message(Request $request)
    {
        return view('index.message.message', [
            'captcha_token' => Captcha::issue(Captcha::TTL_HOME),
        ]);
    }

    public function sendMessage(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            // 'name' => 'required',
            // 'phone' => 'required',
            // 'email' => 'required|email',
            'content' => 'required',
            'captcha' => 'required',
            'captcha_token' => 'required',
        ], [
            'name.required' => '您的称呼不能为空',
            'email.required' => '邮箱地址不能为空',
            'email.email' => '邮箱地址格式有误',
            'phone.required' => '联系电话不能为空',
            'content.required' => '您的需求信息不能为空',
            'captcha.required' => '验证码不能为空',
            'captcha_token.required' => '验证码已失效，请点击图片刷新',
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1002, $msg);
        }

        // 2: 校验验证码（一次性，校验后即销毁）
        $captchaOk = Captcha::verify(
            $request->input('captcha_token'),
            $request->input('captcha')
        );
        if (!$captchaOk) {
            return $this->fail(1002, '验证码不正确。');
        }

        // 3: 获取请求数据
        $data = $request->all();
        $data['ip'] = $request->getClientIp();

        // 4: 执行留言
        $res = MessageLogic::sendMessage($data);
        return $this->fail($res['code'], $res['message']);
    }
}
