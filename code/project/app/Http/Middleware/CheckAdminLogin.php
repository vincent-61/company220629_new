<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取记录的session
        $username = session('login_username');
        $adminId = session('login_admin_id');
        $roleId = session('login_role_id');
        if (empty($username) || empty($adminId) || empty($roleId)) {
            return redirect('/admin/login/index');
        }

        return $next($request);
    }
}
