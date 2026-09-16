<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Logic\Admin\BannerLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class ApiController extends Controller
{

    protected $bannerLogic;

    public function __construct(BannerLogic $bannerLogic)
    {
        $this->bannerLogic = $bannerLogic;
    }

    public function uploadEditorImg(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadEditorImg')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->uploadFail(1001, '上传失败');
        } else {
            $data = [
                'src' => env('APP_URL') . '/upload/editorImg/' . date('Ymd') . '/' . $filename,
                'title' => $filename
            ];
            return $this->uploadSuccess('上传成功', $data);
        }
    }

    public function uploadEditorFile(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadEditorFile')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->uploadFail(1001, '上传失败');
        } else {
            $data = [
                'src' => env('APP_URL') . '/upload/editorFile/' . date('Ymd') . '/' . $filename,
                'title' => $filename
            ];
            return $this->uploadSuccess('上传成功', $data);
        }
    }

    public function uploadBannerImg(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadBannerImg')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->fail(1002, '上传文件失败');
        } else {

            // 4: 增加不同像素图片
            $img2 = Image::make($file)->heighten(372);
            $img2filename = pathinfo($filename, PATHINFO_FILENAME);
            $img2Extension = pathinfo($filename, PATHINFO_EXTENSION);
            $img2->save('upload/bannerImg/' . date('Ymd') . '/' . $img2filename . '_372.' . $img2Extension);

            $data['url'] = '/upload/bannerImg/' . date('Ymd') . '/' . $filename;
            return $this->success('执行成功', $data);
        }
    }

    public function uploadLogo(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadLogo')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->fail(1002, '上传文件失败');
        } else {
            $data['url'] = '/upload/logo/' . date('Ymd') . '/' . $filename;
            return $this->success('执行成功', $data);
        }
    }

    public function uploadCategoryImg(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadCategoryImg')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->fail(1002, '上传文件失败');
        } else {
            // 4: 增加不同像素图片
            $img2 = Image::make($file)->heighten(50);
            $img2filename = pathinfo($filename, PATHINFO_FILENAME);
            $img2Extension = pathinfo($filename, PATHINFO_EXTENSION);
            $img2->save('upload/categoryImg/' . date('Ymd') . '/' . $img2filename . '_50.' . $img2Extension);

            $data['url'] = '/upload/categoryImg/' . date('Ymd') . '/' . $filename;
            return $this->success('执行成功', $data);
        }
    }

    public function uploadProductImg(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadProductImg')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->fail(1002, '上传文件失败');
        } else {
            // 4: 增加不同像素图片
            $img2 = Image::make($file)->heighten(190);
            $img2filename = pathinfo($filename, PATHINFO_FILENAME);
            $img2Extension = pathinfo($filename, PATHINFO_EXTENSION);
            $img2->save('upload/productImg/' . date('Ymd') . '/' . $img2filename . '_190.' . $img2Extension);

            $data = [
                'img_name' => $originalName,
                'url' => '/upload/productImg/' . date('Ymd') . '/' . $filename,
                'size' => $file->getSize(),
            ];
            return $this->success('执行成功', $data);
        }
    }

    public function uploadNewsImg(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadNewsImg')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->fail(1002, '上传文件失败');
        } else {
            // 4: 增加不同像素图片
            $img2 = Image::make($file)->heighten(160);
            $img2filename = pathinfo($filename, PATHINFO_FILENAME);
            $img2Extension = pathinfo($filename, PATHINFO_EXTENSION);
            $img2->save('upload/newsImg/' . date('Ymd') . '/' . $img2filename . '_160.' . $img2Extension);

            $data['url'] = '/upload/newsImg/' . date('Ymd') . '/' . $filename;
            return $this->success('执行成功', $data);
        }
    }

    public function uploadProductFile(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadProductFile')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->fail(1002, '上传文件失败');
        } else {
            $data = [
                'file_name' => $originalName,
                'url' => '/upload/productFile/' . date('Ymd') . '/' . $filename,
                'size' => $file->getSize(),
            ];
            return $this->success('执行成功', $data);
        }
    }

    public function uploadQualificationImg(Request $request)
    {
        // 1: 请求校验
        $validate = Validator::make($request->all(), [
            'file' => 'required|file'
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

        // 3: 上传文件
        $file = $validateData['file'];
        $originalName = $file->getClientOriginalName(); // 原文件名
        $realPath = $file->getRealPath(); // 临时绝对路径
        $filename = time() . '_' . $originalName; // 修改文件名
        $bool = Storage::disk('uploadQualificationImg')->put($filename, file_get_contents($realPath)); // 储存到磁盘相应的路径
        if (!$bool) {
            return $this->fail(1002, '上传文件失败');
        } else {
            // 4: 增加不同像素图片
            $img2 = Image::make($file)->heighten(190);
            $img2filename = pathinfo($filename, PATHINFO_FILENAME);
            $img2Extension = pathinfo($filename, PATHINFO_EXTENSION);
            $img2->save('upload/qualificationImg/' . date('Ymd') . '/' . $img2filename . '_190.' . $img2Extension);

            $img3 = Image::make($file)->heighten(55);
            $img3->save('upload/qualificationImg/' . date('Ymd') . '/' . $img2filename . '_55.' . $img2Extension);

            $data = [
                'img_name' => $originalName,
                'url' => '/upload/qualificationImg/' . date('Ymd') . '/' . $filename,
                'size' => $file->getSize(),
            ];
            return $this->success('执行成功', $data);
        }
    }
}
