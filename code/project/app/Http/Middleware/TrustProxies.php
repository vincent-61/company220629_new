<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * 生产环境在宿主机 nginx 终止 TLS，再以明文 HTTP 转发到 127.0.0.1:8089。
     * 容器只发布 127.0.0.1:8089，外部无法直连，唯一能访问的就是宿主机自己的 nginx，
     * 因此 '*'（信任调用方 IP）在这里是安全的：没有不受信任的来源可以伪造
     * X-Forwarded-* 头。置空则 X-Forwarded-Proto 被忽略，https 页面会生成 http 链接
     * （混合内容、CSS/JS 被拦截），且 throttle 的按 IP 分桶会退化成全站单桶。
     *
     * @var array|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
