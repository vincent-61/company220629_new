<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Index\IndexController;
use App\Http\Controllers\Index\ProductController;
use App\Http\Controllers\Index\NewsController;
use App\Http\Controllers\Index\QualificationController;
use App\Http\Controllers\Index\MessageController;
use App\Http\Controllers\Index\CaptchaController;

use App\Http\Controllers\Wap\IndexController as WapIndexController;
use App\Http\Controllers\Wap\ProductController as WapProductController;
use App\Http\Controllers\Wap\NewsController as WapNewsController;
use App\Http\Controllers\Wap\QualificationController as WapQualificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 注意：获取数据用post，获取页面用get，中间件checkAuth会根据请求类型来判断返回的形式

// 前台
Route::get('/', [IndexController::class, 'index']);
Route::get('/index.html', [IndexController::class, 'index']); // 首页

// 公司简介
Route::get('/about.html', [IndexController::class, 'about']); // 公司简介
Route::get('/contact.html', [IndexController::class, 'contact']); // 联系我们

// 产品展示
Route::get('/product.html', [ProductController::class, 'category']); // 产品栏目
Route::get('/product/list/{category_id}.html', [ProductController::class, 'category']); // 产品栏目
Route::get('/product/detail/{product_id}.html', [ProductController::class, 'product']); // 产品详情

// 新闻中心
Route::get('/news.html', [NewsController::class, 'category']); // 新闻栏目
Route::get('/news/list/{category_id}.html', [NewsController::class, 'category']); // 新闻栏目
Route::get('/news/detail/{news_id}.html', [NewsController::class, 'news']); // 新闻详情

// 资质认证
Route::get('/qualification.html', [QualificationController::class, 'category']); // 资质栏目
Route::get('/qualification/list/{category_id}.html', [QualificationController::class, 'category']); // 资质栏目
Route::get('/qualification/detail/{news_id}.html', [QualificationController::class, 'qualification']); // 资质详情

// 在线留言
Route::get('/message.html', [MessageController::class, 'message']); // 联系页面
Route::post('/sendMessage', [MessageController::class, 'sendMessage']); // 新闻栏目

// 验证码（前台不能用 /captcha，该路径被 mews/captcha 包占用）
Route::get('/verifyCode', [CaptchaController::class, 'index']);
// refresh 会签发新的 Redis key，按 IP 限流；index 只渲染或返回 410，不限流
Route::get('/verifyCode/refresh', [CaptchaController::class, 'refresh'])->middleware('throttle:30,1');


// 手机端
Route::prefix('/wap')->group(function () {
    // 前台
    Route::get('/', [WapIndexController::class, 'index']);
    Route::get('/index.html', [WapIndexController::class, 'index']); // 首页

    // 公司简介
    Route::get('/about.html', [WapIndexController::class, 'about']); // 公司简介
    Route::get('/contact.html', [WapIndexController::class, 'contact']); // 联系我们

    // 产品展示
    Route::get('/product.html', [WapProductController::class, 'category']); // 产品栏目
    Route::get('/product/list/{category_id}.html', [WapProductController::class, 'category']); // 产品栏目
    Route::get('/product/detail/{product_id}.html', [WapProductController::class, 'product']); // 产品详情

    // 新闻中心
    Route::get('/news.html', [WapNewsController::class, 'category']); // 新闻栏目
    Route::get('/news/list/{category_id}.html', [WapNewsController::class, 'category']); // 新闻栏目
    Route::get('/news/detail/{news_id}.html', [WapNewsController::class, 'news']); // 新闻详情

    // 资质认证
    Route::get('/qualification.html', [WapQualificationController::class, 'category']); // 资质栏目
    Route::get('/qualification/list/{category_id}.html', [WapQualificationController::class, 'category']); // 资质栏目
    Route::get('/qualification/detail/{news_id}.html', [WapQualificationController::class, 'qualification']); // 资质详情
});
