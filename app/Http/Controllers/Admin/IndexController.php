<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\IndexLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Predis\Client;

class IndexController extends Controller {

    protected $indexLogic;

    public function __construct(IndexLogic $indexLogic)
    {
        $this->indexLogic = $indexLogic;
    }

    /**
     * 首页框架
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-08
     */
    public function index()
    {
        // 获取当前用户信息
        $adminInfo = $this->indexLogic->getAdminInfo(session('login_admin_id'));
        if (!isset($adminInfo['code']) || $adminInfo['code'] !== 0 && empty($adminInfo['data'])) {
            return redirect('/admin/login/index');
        }

        return view('admin.index.index', ['adminInfo' => $adminInfo['data']]);
    }

    /**
     * 首页
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-09
     */
    public function home()
    {
        $adminInfo = $this->indexLogic->getAdminInfo(session('login_admin_id'));
        if (!isset($adminInfo['code']) || $adminInfo['code'] !== 0 && empty($adminInfo['data'])) {
            return redirect('/admin/login/index');
        }

        return view('admin.index.home', ['adminInfo' => $adminInfo['data']]);
    }

    /**
     * 获取菜单初始化
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-09
     */
    public function init()
    {
        // 1: 获取当前用户的角色id
        $roleId = session('login_role_id');

        // 2: 获取平台首页
        $homeInfo = [
            'title' => '首页',
            'href' => 'index/home',
        ];

        // 3: 获取平台logo
        $logoInfo = [
            'title' => '官网管理',
            'image' => '/static_admin/images/logo.png',
        ];

        // 4: 根据角色获取菜单
        $menuList = $this->indexLogic->getMenuList($roleId);
        $menuListTree = [];
        if (isset($menuList['code']) && $menuList['code'] == 0 && !empty($menuList['data'])) {
            // 4.1: 格式化菜单列表
            $menuListTree = $this->indexLogic->getMenuListTree(0, $menuList['data']);
        }

        // 5: 获取菜单
        $systemInit = [
            'homeInfo' => $homeInfo,
            'logoInfo' => $logoInfo,
            'menuInfo' => $menuListTree,
        ];

        return response()->json($systemInit);
    }

    /**
     * 修改密码页面
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-09
     */
    public function userPassword()
    {
        return view('admin.index.user_password');
    }

    /**
     * 执行修改密码
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-09
     */
    public function updatePassword(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'again_password' => 'required|min:8',
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

        // 3: 执行修改密码
        $adminId = session('login_admin_id');
        $result = $this->indexLogic->updatePassword($adminId, $validateData['old_password'], $validateData['new_password'], $validateData['again_password']);
        if ($result['code'] !== 0) {
            return $this->fail($result['code'], $result['message']);
        }

        return $this->success('修改成功');
    }

    /**
     * 修改个人资料页面
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-10
     */
    public function userSetting()
    {
        // 1: 获取当前用户信息
        $adminInfo = $this->indexLogic->getAdminInfo(session('login_admin_id'));
        if (!isset($adminInfo['code']) || $adminInfo['code'] !== 0 && empty($adminInfo['data'])) {
            return redirect('/admin/login/index');
        }

        // 2: 获取当前用户角色信息
        $roleInfo = $this->indexLogic->getRoleInfo(session('login_role_id'));
        if (!isset($adminInfo['code']) || $adminInfo['code'] !== 0 && empty($adminInfo['data'])) {
            return redirect('/admin/login/index');
        }

        // 3: 组装显示数据
        $data = [
            'adminInfo' => $adminInfo['data'],
            'roleInfo' => $roleInfo['data'],
        ];

        return view('admin.index.user_setting', $data);
    }

    /**
     * 修改个人资料
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-10
     */
    public function updateSetting(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'real_name' => 'required',
            'phone' => 'required',
            'email' => 'required',
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

        // 3: 执行修改密码
        $adminId = session('login_admin_id');
        $result = $this->indexLogic->updateSetting($adminId, $validateData);
        if ($result['code'] !== 0) {
            return $this->fail($result['code'], $result['message']);
        }

        return $this->success('修改成功');
    }

    public function clearCache()
    {
        /**
         * @var  $redis Client
         */
        $redis = Redis::connection();
        $redisPrefix = env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_');
        $cacheKeyArr = [
            '*LayoutDataCache*',
            '*IndexDataCache*',
            '*ProductListDataCache*',
            '*ProductDetailDataCache*',
            '*CommentListDataCache*',
        ];

        foreach ($cacheKeyArr as $value) {
            $cacheKey = $redis->keys($value);
            if (!empty($cacheKey)) {
                foreach ($cacheKey as $keyValue) {
                    $keyValue = ltrim($keyValue, $redisPrefix);
                    $redis->del($keyValue);
                }

            }
        }

        return response()->json([
            'code' => 1,
            "msg" => "清除服务端缓存成功"
        ]);
    }

}
