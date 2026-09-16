<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CaptchaController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\IndexController;
use App\Http\Controllers\Admin\LoginLogController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\QualificationCategoryController;
use App\Http\Controllers\Admin\QualificationController;
use App\Http\Controllers\Admin\MessageController;

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

// 后台
Route::prefix('/admin')->group(function () {

    // 验证码（登录页使用，无需登录态）
    Route::get('/captcha', [CaptchaController::class, 'index']);
    // refresh 会签发新的 Redis key，按 IP 限流；index 只渲染或返回 410，不限流
    Route::get('/captcha/refresh', [CaptchaController::class, 'refresh'])->middleware('throttle:30,1');

    Route::get('/', [IndexController::class, 'index']);

    // 通用接口
    Route::prefix('/api')->middleware(['check.admin.login'])->group(function () {
        Route::get('/test', [ApiController::class, 'test']); // 上传编辑器图片
        Route::post('/uploadEditorImg', [ApiController::class, 'uploadEditorImg']); // 上传编辑器图片
        Route::post('/uploadEditorFile', [ApiController::class, 'uploadEditorFile']); // 上传编辑器图片

        Route::post('/uploadBannerImg', [ApiController::class, 'uploadBannerImg']); // 上传轮播图片
        Route::post('/uploadLogo', [ApiController::class, 'uploadLogo']); // 上传Logo

        Route::post('/uploadCategoryImg', [ApiController::class, 'uploadCategoryImg']); // 上传产品图片
        Route::post('/uploadProductImg', [ApiController::class, 'uploadProductImg']); // 上传产品图片
        Route::post('/uploadProductFile', [ApiController::class, 'uploadProductFile']); // 上传产品图片
        Route::post('/uploadNewsImg', [ApiController::class, 'uploadNewsImg']); // 上传产品图片
        Route::post('/uploadQualificationImg', [ApiController::class, 'uploadQualificationImg']); // 上传产品图片
    });

    // 登录
    Route::prefix('/login')->group(function () {
        // 每次渲染都会 Captcha::issue() 写一个 Redis 键，按 IP 限流
        Route::get('/index', [LoginController::class, 'index'])->middleware('throttle:60,1'); // 登录首页
        Route::post('/checkLogin', [LoginController::class, 'checkLogin']); // 登录校验
        Route::post('/loginOut', [LoginController::class, 'loginOut']); // 登录校验
    });

    // 登录日志管理
    Route::prefix('/loginLog')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [LoginLogController::class, 'index']); // 日志页

        Route::post('/getLoginLogList', [LoginLogController::class, 'getLoginLogList']); // 获取登录日志
    });

    // 首页
    Route::prefix('/index')->middleware('check.admin.login')->group(function () {
        Route::get('/index', [IndexController::class, 'index']); // 首页框架
        Route::get('/init', [IndexController::class, 'init']); // 获取菜单
        Route::get('/home', [IndexController::class, 'home']); // 首页
        Route::get('/userPassword', [IndexController::class, 'userPassword']); // 修改密码页
        Route::get('/userSetting', [IndexController::class, 'userSetting']); // 修改个人资料页
        Route::get('/clearCache', [IndexController::class, 'clearCache']); // 清空缓存

        Route::post('/updatePassword', [IndexController::class, 'updatePassword']); // 修改密码
        Route::post('/updateSetting', [IndexController::class, 'updateSetting']); // 修改个人资料
    });

    // 平台管理
    Route::prefix('/platform')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [PlatformController::class, 'index']); // 基础信息页

        Route::post('/doEdit', [PlatformController::class, 'doEdit']); // 修改
    });

    // 轮播图
    Route::prefix('/banner')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [BannerController::class, 'index']); // 轮播图列表
        Route::get('/edit', [BannerController::class, 'edit']); // 添加/编辑

        Route::post('/getBannerList', [BannerController::class, 'getBannerList']); // 获取列表
        Route::post('/doEditImg', [BannerController::class, 'doEditImg']); // 上传图片
        Route::post('/doEdit', [BannerController::class, 'doEdit']); // 修改
        Route::post('/setBannerStatus', [BannerController::class, 'setBannerStatus']); // 设置状态
    });

    // 分类管理
    Route::prefix('/productCategory')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [ProductCategoryController::class, 'index']); // 分类列表
        Route::get('/edit', [ProductCategoryController::class, 'edit']); // 添加/编辑

        Route::post('/getProductCategoryList', [ProductCategoryController::class, 'getProductCategoryList']); // 获取列表
        Route::post('/doEdit', [ProductCategoryController::class, 'doEdit']); // 修改
        Route::post('/setProductCategoryStatus', [ProductCategoryController::class, 'setProductCategoryStatus']); // 设置状态
    });

    // 产品管理
    Route::prefix('/product')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [ProductController::class, 'index']); // 产品列表
        Route::get('/edit', [ProductController::class, 'edit']); // 添加/编辑

        Route::post('/getProductList', [ProductController::class, 'getProductList']); // 获取列表
        Route::post('/doEdit', [ProductController::class, 'doEdit']); // 修改
        Route::post('/setProductStatus', [ProductController::class, 'setProductStatus']); // 设置状态
        Route::post('/setProductRecommend', [ProductController::class, 'setProductRecommend']); // 设置推荐
        Route::post('/doDelBatch', [ProductController::class, 'doDelBatch']); // 批量删除商品
    });

    // 新闻分类
    Route::prefix('/newsCategory')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [NewsCategoryController::class, 'index']); // 分类列表
        Route::get('/edit', [NewsCategoryController::class, 'edit']); // 添加/编辑

        Route::post('/getNewsCategoryList', [NewsCategoryController::class, 'getNewsCategoryList']); // 获取列表
        Route::post('/doEdit', [NewsCategoryController::class, 'doEdit']); // 修改
        Route::post('/setNewsCategoryStatus', [NewsCategoryController::class, 'setNewsCategoryStatus']); // 设置状态
    });

    // 新闻管理
    Route::prefix('/news')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [NewsController::class, 'index']); // 新闻列表
        Route::get('/edit', [NewsController::class, 'edit']); // 添加/编辑

        Route::post('/getNewsList', [NewsController::class, 'getNewsList']); // 获取列表
        Route::post('/doEdit', [NewsController::class, 'doEdit']); // 修改
        Route::post('/setNewsStatus', [NewsController::class, 'setNewsStatus']); // 设置状态
    });

    // 资质分类
    Route::prefix('/qualificationCategory')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [QualificationCategoryController::class, 'index']); // 分类列表
        Route::get('/edit', [QualificationCategoryController::class, 'edit']); // 添加/编辑

        Route::post('/getQualificationCategoryList', [QualificationCategoryController::class, 'getQualificationCategoryList']); // 获取列表
        Route::post('/doEdit', [QualificationCategoryController::class, 'doEdit']); // 修改
        Route::post('/setQualificationCategoryStatus', [QualificationCategoryController::class, 'setQualificationCategoryStatus']); // 设置状态
    });

    // 资质管理
    Route::prefix('/qualification')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [QualificationController::class, 'index']); // 资质列表
        Route::get('/edit', [QualificationController::class, 'edit']); // 添加/编辑

        Route::post('/getQualificationList', [QualificationController::class, 'getQualificationList']); // 获取列表
        Route::post('/doEdit', [QualificationController::class, 'doEdit']); // 修改
        Route::post('/setQualificationStatus', [QualificationController::class, 'setQualificationStatus']); // 设置状态
    });

    // 留言管理
    Route::prefix('/message')->middleware(['check.admin.login'])->group(function () {
        Route::get('/index', [MessageController::class, 'index']); // 产品列表
        Route::get('/edit', [MessageController::class, 'edit']); // 添加/编辑

        Route::post('/getMessageList', [MessageController::class, 'getMessageList']); // 获取列表
        Route::post('/setMessageStatus', [MessageController::class, 'setMessageStatus']); // 设置状态
        Route::post('/doDelBatch', [MessageController::class, 'doDelBatch']); // 批量删除商品
    });

});
