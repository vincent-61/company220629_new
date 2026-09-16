<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\LoginLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller {

    protected $loginLogic;

    public function __construct(LoginLogic $loginLogic)
    {
        $this->loginLogic = $loginLogic;
    }

    /**
     * 登录页
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-06
     */
    public function index()
    {
        return view('admin.login.index');
    }

    /**
     * 检查登录
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-06
     */
    public function checkLogin(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'captcha' => 'required|captcha',
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1001, $msg);
        }

        // 2: 执行检测登录操作
        $username = $request->input('username');
        $password = $request->input('password');
        $ip = $request->getClientIp();;
        $checkLogin = $this->loginLogic->checkLogin($username, $password, $ip);
        if ($checkLogin['code'] !== 0) {
            return $this->fail($checkLogin['code'], $checkLogin['message']);
        } else {
            return $this->success('登录成功');
        }
    }

    /**
     * 退出登录
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-16
     */
    public function loginOut()
    {
        session([
            'login_admin_id' => null,
            'login_username' => null,
            'login_role_id' => null,
        ]);

        return $this->success('退出登录成功');
    }



}
