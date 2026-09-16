<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\LoginLog;
use App\Models\Admin;

class LoginLogLogic extends BaseLogic
{

    public function getLoginLogList($request)
    {
        // 1: 非空校验
        if (empty($request['page']) || empty($request['limit'])) {
            return $this->resMsg(1001, '请求数据有误');
        }

        // 2: 组装条件
        $where = [];
        if (!empty($request['real_name'])) {
            $where[] = ['admin.real_name', 'like', '%' . $request['real_name'] . '%'];
        }

        // 3: 获取数据
        $column = ['login_log.*', 'admin.username', 'admin.real_name'];
        $count = LoginLog::leftJoin('admin', 'login_log.admin_id', '=', 'admin.admin_id')
            ->where($where)->count();
        $list = LoginLog::leftJoin('admin', 'login_log.admin_id', '=', 'admin.admin_id')
            ->where($where)->forPage($request['page'], $request['limit'])->orderBy('login_log.created_at', 'desc')->get($column)->toArray();

        // 4: 格式化数据
        if (!empty($list)) {

            // 4.1: 获取列表的创建用户名称
            $adminIdList = array_column($list, 'admin_id');
            $adminList = Admin::whereIn('admin_id', $adminIdList)->get(['admin_id', 'username', 'real_name']);
            $usernameList = [];
            if (!empty($adminList)) {
                $usernameList = array_column($adminList->toArray(), 'username', 'admin_id');
                $realNameList = array_column($adminList->toArray(), 'real_name', 'admin_id');
            }

            foreach ($list as &$value) {
                $value['created_at'] = !empty($value['created_at']) ? date("Y-m-d H:i:s", $value['created_at']) : '';

                // 获取创建用户
                $value['username'] = $value['real_name'] = '';
                if (!empty($value['admin_id'])) {
                    $value['username'] = !empty($usernameList[$value['admin_id']]) ? $usernameList[$value['admin_id']] : '';
                    $value['real_name'] = !empty($realNameList[$value['admin_id']]) ? $realNameList[$value['admin_id']] : '';
                }
            }
        }

        return ['list' => $list, 'count' => $count];
    }

}
