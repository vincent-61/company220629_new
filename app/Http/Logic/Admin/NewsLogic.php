<?php

namespace App\Http\Logic\Admin;

use App\Http\Logic\BaseLogic;
use App\Models\News;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsLogic extends BaseLogic
{
    public function getNewsList($request)
    {
        // 1: 非空校验
        if (empty($request['page']) || empty($request['limit'])) {
            return $this->resMsg(1001, '请求数据有误');
        }

        // 2: 获取数据
        $where = [
            ['news.status', '!=', 3]
        ];
        if (!empty($request['category_id'])) {
            $where[] = ['news.category_id', '=', $request['category_id']];
        }
        if (!empty($request['title'])) {
            $where[] = ['news.title', 'like', '%' . $request['title'] . '%'];
        }
        if (!empty($request['status'])) {
            $where[] = ['news.status', '=', $request['status']];
        }
        $column = ['news.*', 'news_category.category_name'];
        $count = News::where($where)->count();
        $list = News::leftJoin('news_category', 'news.category_id', '=', 'news_category.category_id')
            ->where($where)->forPage($request['page'], $request['limit'])->orderBy('news.news_id', 'desc')->get($column);

        // 3: 格式化数据
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$value) {
                $value['news_time'] = !empty($value['news_time']) ? date("Y-m-d H:i:s", $value['news_time']) : '';
                $value['created_at'] = !empty($value['created_at']) ? date("Y-m-d H:i:s", $value['created_at']) : '';
                $value['updated_at'] = !empty($value['updated_at']) ? date("Y-m-d H:i:s", $value['updated_at']) : '';
            }
        }

        return ['list' => $list, 'count' => $count];
    }

    public function getNewsInfo($newsId)
    {
        // 1: 非空判断
        if (empty($newsId)) {
            return [];
        }

        // 2: 获取信息
        $info = News::leftJoin('news_category', 'news.category_id', '=', 'news_category.category_id')
            ->where(['news.news_id' => $newsId])->first();
        if (empty($info)) {
            return [];
        }
        $info['news_time'] = !empty($info['news_time']) ? date('Y-m-d H:i:s', $info['news_time']) : '';
        $info = $info->toArray();

        // 3: 返回结果
        return $info;
    }

    public function doEdit($data)
    {
        // 1: 非空判断
        if (empty($data)) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行新增
        try {
            DB::beginTransaction();

            // 2.1: 添加新闻
            $newsData = [
                'category_id' => !empty($data['category_id']) ? $data['category_id'] : '',
                'author' => !empty($data['title']) ? $data['author'] : '',
                'title' => !empty($data['title']) ? $data['title'] : '',
                'img' => !empty($data['img']) ? $data['img'] : '',
                'content' => !empty($data['content']) ? $data['content'] : '',
                'sort' => !empty($data['sort']) ? $data['sort'] : 0,
                'news_time' => !empty($data['news_time']) ? strtotime($data['news_time']) : 0,
            ];
            if (!empty($data['news_id'])) {
                $newsData['updated_at'] = time();
                News::where('news_id', $data['news_id'])->update($newsData);
            } else {
                $newsData['created_at'] = time();
                News::insert($newsData);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[NewsLogic doEdit] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }

    public function setNewsStatus($request)
    {
        // 1: 非空判断
        if (empty($request['news_id']) || empty($request['status'])) {
            return $this->resMsg(1001, '请求参数有误');
        }

        // 2: 执行修改
        try {
            $data = [
                'status' => $request['status'],
                'updated_at' => time(),
            ];
            News::where('news_id', $request['news_id'])->update($data);
        } catch (\Exception $e) {
            Log::error('[NewsLogic setStatus] message: ' . $e->getMessage());
            return $this->resMsg(1002, '执行失败');
        }

        return $this->resMsg(0, '执行成功');
    }
}
