<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\Admin;
use App\Models\AdminMenu;
use App\Models\AdminRole;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Log;

class IndexLogic extends BaseLogic
{
    /**
     * 获取菜单列表
     *
     * @param $roleId
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-08
     */
    public function getMenuList($roleId)
    {
        try {
            $menuWhere = [
                ['status', '=', 1],
                ['type', '!=', 4],
            ];
            $menuColumn = ['menu_id', 'parent_id', 'title', 'href', 'icon', 'permit'];

            if ($roleId == 1) { // 1: 超级管理员菜单
                $menuList = AdminMenu::where($menuWhere)
                    ->orderBy('sort', 'DESC')->get($menuColumn)->toArray();

            } else { // 2: 非超级管理员
                // 2.1: 获取当前权限的菜单ID
                $roleInfo = AdminRole::where('role_id', $roleId)->first()->toArray();
                $rules = !empty($roleInfo['rules']) ? explode(',', $roleInfo['rules']) : [];

                // 2.2: 根据菜单ID获取菜单列表
                $menuList = AdminMenu::where($menuWhere)->whereIn('menu_id', $rules)
                    ->orderBy('sort', 'DESC')->get($menuColumn)->toArray();
            }

        } catch (\Exception $e) {
            Log::error('[IndexLogic getMenuList] message: ' . $e->getMessage());
            return $this->resMsg(1001, '请求有误');
        }

        return $this->resMsg(0, '成功', $menuList);
    }

    /**
     * 获取菜单树
     *
     * @param $parentId
     * @param $menuList
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-09
     */
    public function getMenuListTree($parentId, $menuList)
    {
        $treeList = [];
        foreach ($menuList as $value) {
            if ($value['parent_id'] == $parentId) {
                $node = (array)$value;
                $child = $this->getMenuListTree($value['menu_id'], $menuList);
                if (!empty($child)) {
                    $node['child'] = $child;
                }
                $treeList[] = $node;
            }
        }
        return $treeList;
    }

    /**
     * 获取用户数据
     *
     * @param $adminId
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-09
     */
    public function getAdminInfo($adminId)
    {
        // 1: 非空判断
        if (empty($adminId)) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 获取信息
        $adminInfo = Admin::where(['admin_id' => $adminId, 'status' => 1])->first();
        if (empty($adminInfo)) {
            return $this->resMsg(1002, '查询数据为空');
        } else {
            $adminInfo['last_login_time'] = '';
            $logInfo = LoginLog::where(['admin_id' => $adminId])->orderBy('created_at', 'desc')->forPage(2, 1)->first();
            if (!empty($logInfo)) {
                $logInfo = $logInfo->toArray();
                $adminInfo['last_login_time'] = !empty($logInfo['created_at']) ? date('Y-m-d H:i', $logInfo['created_at']) : '';
            }
        }

        return $this->resMsg(0, '成功', $adminInfo->toArray());
    }

    /**
     * 修改密码
     *
     * @param $adminId
     * @param $oldPwd
     * @param $newPwd
     * @param $againPwd
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-09
     */
    public function updatePassword($adminId, $oldPwd, $newPwd, $againPwd)
    {
        // 1: 非空判断
        if (empty($adminId) || empty($oldPwd) || empty($newPwd) || empty($againPwd)) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 密码重复校验
        if ($newPwd != $againPwd) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 3: 旧密码校验
        $adminInfo = $this->getAdminInfo($adminId);
        if (!empty($adminInfo['data']['password']) && !empty($adminInfo['data']['salt'])) {
            $checkPassword = checkPassword($oldPwd, $adminInfo['data']['password'], $adminInfo['data']['salt']);
            if ($checkPassword === false) {
                return $this->resMsg(1002, '旧密码错误');
            }
        } else {
            return $this->resMsg(1002, '旧密码校验失败');
        }

        // 4: 修改密码
        try {
            $newPwdEncode = makePassword($newPwd, $adminInfo['data']['salt']);
            Admin::where('admin_id', $adminId)->update(['password' => $newPwdEncode]);
        } catch (\Exception $e) {
            Log::error('[IndexLogic updatePassword] message: ' . $e->getMessage());
            return $this->resMsg(1002, '旧密码错误');
        }

        return $this->resMsg(0, '修改成功');
    }

    /**
     * 获取用户角色数据
     *
     * @param $roleId
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-10
     */
    public function getRoleInfo($roleId)
    {
        // 1: 非空判断
        if (empty($roleId)) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 获取信息
        $info = AdminRole::where(['role_id' => $roleId, 'status' => 1])->first()->toArray();
        if (empty($info)) {
            return $this->resMsg(1002, '查询数据为空');
        }

        return $this->resMsg(0, '成功', $info);
    }

    /**
     * 修改个人资料
     *
     * @param $adminId
     * @param $data
     *
     * @return array
     *
     * @author VincentZheng <1092161320@qq.com> 2021-06-10
     */
    public function updateSetting($adminId, $data)
    {
        // 1: 非空判断
        if (empty($adminId) || empty($data)) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 修改密码
        try {
            Admin::where('admin_id', $adminId)->update($data);
        } catch (\Exception $e) {
            Log::error('[IndexLogic updateSetting] message: ' . $e->getMessage());
            return $this->resMsg(1002, '修改失败');
        }

        return $this->resMsg(0, '修改成功');
    }


}
