<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\NewsLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NewsController extends Controller
{

    protected $newsLogic;

    public function __construct(NewsLogic $newsLogic)
    {
        $this->newsLogic = $newsLogic;
    }

    public function index()
    {
        return view('admin.news.index');
    }

    public function getNewsList(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'page' => '',
            'limit' => '',
            'category_id' => '',
            'title' => '',
            'status' => '',
        ]);
        if ($validate->fails()) {
            $msg = $validate->errors()->first();
            return $this->fail(1001, $msg);
        }

        // 2: 获取请求数据
        try {
            $validateData = $validate->validate();
            $validateData['page'] = !empty($validateData['page']) ? $validateData['page'] : 1;
            $validateData['limit'] = !empty($validateData['limit']) ? $validateData['limit'] : 15;

        } catch (ValidationException $e) {
            return $this->fail(1001, $e->getMessage());
        }

        // 3: 获取数据
        $list = $this->newsLogic->getNewsList($validateData);
        return response()->json([
            'code' => 0,
            'msg' => '查询成功',
            'count' => $list['count'],
            'data' => $list['list']
        ]);

    }

    public function edit(Request $request)
    {
        // 1: 获取信息
        $newsId = $request->get('news_id');
        $info = [];
        if (!empty($newsId)) {
            $info = $this->newsLogic->getNewsInfo($newsId);
        }

        // 2: 组装显示数据
        $data['newsInfo'] = $info;

        return view('admin.news.edit', $data);
    }

    public function doEdit(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'news_id' => '',
            'category_id' => 'required',
            'author' => 'required',
            'title' => 'required',
            'img' => 'required',
            'content' => 'required',
            'sort' => 'required',
            'news_time' => '',
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

        // 3: 执行修改
        $res = $this->newsLogic->doEdit($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }

    public function setNewsStatus(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'news_id' => 'required',
            'status' => 'required'
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

        // 3: 修改数据
        $res = $this->newsLogic->setNewsStatus($validateData);
        if ($res['code'] === 0) {
            return $this->success('执行成功');
        } else {
            return $this->fail($res['code'], $res['message']);
        }
    }
}
