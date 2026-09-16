<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\LoginLog;
use App\Models\AdminRole;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

class LoginLogic extends BaseLogic
{

    /**
     * 检测登录
     *
     * @param $username
     * @param $password
     * @param $ip
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-06
     */
    public function checkLogin($username, $password, $ip): array
    {
        // 1: 非空判断
        if (empty($username) || empty($password)) {
            return $this->resMsg(1001, '参数不可为空', []);
        }

        // 2: 判断用户是否存在
        $adminWhere = [
            'status' => 1,
            'username' => $username,
        ];
        $adminInfo = Admin::where($adminWhere)->first();
        if (empty($adminInfo) || empty($adminInfo['role_id'])) {
            return $this->resMsg(1002, '用户名或密码错误');
        }

        // 3: 判断角色是否禁用
        $roleWhere = [
            'status' => 1,
            'role_id' => $adminInfo['role_id']
        ];
        $roleInfo = AdminRole::where($roleWhere)->first();
        if (empty($roleInfo)) {
            return $this->resMsg(1002, '当前角色已被禁用');
        }

        // 4: 判断用户密码错误次数
        /**
         * @var  $redis Client
         */
        $redis = Redis::connection();
        $checkLoginCount = $redis->get('checkLoginCount:' . $adminInfo['admin_id']);
        if (!empty($checkLoginCount) && $checkLoginCount >= 5) {
            return $this->resMsg(1003, '密码错误次数过多，请30分钟后再试，或联系管理员重设密码');
        }

        // 5: 判断用户密码是否正确
        $checkPassword = checkPassword($password, $adminInfo['password'], $adminInfo['salt']);
        if ($checkPassword === false) {
            $checkLoginCount = !empty($checkLoginCount) ? $checkLoginCount + 1 : 1;
            $redis->setex('checkLoginCount:' . $adminInfo['admin_id'], 1800, $checkLoginCount);

            $msg = '用户名或密码错误';
            if ($checkLoginCount > 1 && $checkLoginCount < 5) {
                $msg .= '，还剩 ' . (5 - $checkLoginCount) . ' 次机会';
            } else if ($checkLoginCount == 5) {
                $msg = '密码错误次数过多，请30分钟后再试，或联系管理员重设密码';
            }
            Log::alert('[LoginLogic checkLogin] message: admin_id 为' . $adminInfo['admin_id'] . ' 用户， 尝试登录失败(' . $checkLoginCount . ')。');

            return $this->resMsg(1002, $msg);
        }

        // 6: 保存session
        session([
            'login_admin_id' => $adminInfo['admin_id'],
            'login_username' => $adminInfo['username'],
            'login_role_id' => $adminInfo['role_id'],
        ]);

        // 7: 更新登录时间
        $redis->del('checkLoginCount:' . $adminInfo['admin_id']);
        Admin::where('admin_id', $adminInfo['admin_id'])->update(['last_login_time' => time()]);

        // 8: 记录日志
        $logData = [
            'admin_id' => $adminInfo['admin_id'],
            'operate' => '用户登录',
            'ip' => !empty($ip) ? $ip : '',
            'created_at' => time()
        ];
        LoginLog::insert($logData);

        // 9: 返回成功
        return $this->resMsg(0, '成功');
    }


}
