<?php

namespace App\Common;

use Illuminate\Support\Facades\Redis;

/**
 * 验证码（纯 Redis token 方案）
 *
 * mews/captcha 依赖 BcryptHasher + Session，在 nginx + php-fpm 容器环境下
 * password_verify 不稳定，故改为自实现：Redis 存明文验证码，token 作为 key。
 *
 * @author VincentZheng <1092161320@qq.com> 2026-09-16
 */
class Captcha
{
    /** Redis key 前缀 */
    const KEY_PREFIX = 'captcha:';

    /** 后台登录验证码有效期（秒） */
    const TTL_ADMIN = 60;

    /** 前台留言验证码有效期（秒）—— 需要填表单，放宽到 5 分钟 */
    const TTL_HOME = 300;

    /** 字符集：剔除 0/o、1/l/i、5/s、8/b 等易混淆字符 */
    const CHARSET = '2346789abcdefghjkmnpqrtuvwxyzABCDEFGHJKMNPQRTUVWXYZ';

    /** 验证码位数 */
    const LENGTH = 4;

    const WIDTH = 120;
    const HEIGHT = 36;

    /**
     * 签发验证码，返回 token
     *
     * @param int $ttl 有效期（秒）
     *
     * @return string
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public static function issue(int $ttl = self::TTL_ADMIN): string
    {
        $token = md5(uniqid((string) mt_rand(), true));
        Redis::setex(self::KEY_PREFIX . $token, $ttl, self::generateCode());
        return $token;
    }

    /**
     * 一次性校验：无论成功失败都销毁 token，防止重放
     *
     * @param string|null $token
     * @param string|null $input
     *
     * @return bool
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public static function verify(?string $token, ?string $input): bool
    {
        if (empty($token) || empty($input)) {
            return false;
        }

        $code = Redis::get(self::KEY_PREFIX . $token);
        Redis::del(self::KEY_PREFIX . $token);

        if (empty($code)) {
            return false;
        }

        return strtolower(trim($input)) === strtolower($code);
    }

    /**
     * 按 token 渲染 PNG 图片
     *
     * @param string $token
     *
     * @return string|null PNG 二进制；token 无效或已过期返回 null
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    public static function render(string $token): ?string
    {
        if (empty($token)) {
            return null;
        }

        $code = Redis::get(self::KEY_PREFIX . $token);
        if (empty($code)) {
            return null;
        }

        $width = self::WIDTH;
        $height = self::HEIGHT;
        $img = imagecreatetruecolor($width, $height);

        // 背景
        $bg = (int) imagecolorallocate($img, 236, 242, 244);
        imagefilledrectangle($img, 0, 0, $width, $height, $bg);

        // 干扰线
        for ($i = 0; $i < 6; $i++) {
            $c = (int) imagecolorallocate($img, rand(120, 200), rand(120, 200), rand(120, 200));
            imageline($img, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $c);
        }

        // 噪点
        for ($i = 0; $i < 100; $i++) {
            $c = (int) imagecolorallocate($img, rand(100, 200), rand(100, 200), rand(100, 200));
            imagesetpixel($img, rand(0, $width), rand(0, $height), $c);
        }

        // 字符
        $font = self::getFontPath();
        $textColor = (int) imagecolorallocate($img, 44, 62, 80);
        $length = strlen($code);
        for ($i = 0; $i < $length; $i++) {
            $x = 10 + $i * 26;
            $y = rand(22, 30);
            $angle = rand(-15, 15);
            if ($font !== null) {
                imagettftext($img, 20, $angle, $x, $y, $textColor, $font, $code[$i]);
            } else {
                imagestring($img, 5, $x, $y - 12, $code[$i], $textColor);
            }
        }

        ob_start();
        imagepng($img);
        $binary = ob_get_clean();
        imagedestroy($img);

        return $binary === false ? null : $binary;
    }

    /**
     * 生成随机验证码
     *
     * @return string
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    private static function generateCode(): string
    {
        $charset = self::CHARSET;
        $max = strlen($charset) - 1;
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= $charset[rand(0, $max)];
        }
        return $code;
    }

    /**
     * 查找可用 TTF 字体（容器内由 fonts-dejavu-core 提供）
     *
     * @return string|null
     *
     * @author VincentZheng <1092161320@qq.com> 2026-09-16
     */
    private static function getFontPath(): ?string
    {
        $candidates = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }
}
