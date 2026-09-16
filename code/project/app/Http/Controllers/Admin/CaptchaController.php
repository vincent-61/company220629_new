<?php

namespace App\Http\Controllers\Admin;

use App\Common\Captcha;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 后台验证码
 *
 * @author VincentZheng <1092161320@qq.com> 2026-09-16
 */
class CaptchaController extends Controller
{
    /**
     * 输出验证码图片
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public function index(Request $request)
    {
        $png = Captcha::render((string) $request->input('token', ''));
        if ($png === null) {
            // token 缺失或已过期，前端收到非图片响应会触发 onerror → refreshCaptcha()
            return response('', 410);
        }

        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * 刷新验证码，返回新 token
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public function refresh()
    {
        return response()->json(['token' => Captcha::issue(Captcha::TTL_ADMIN)]);
    }
}
