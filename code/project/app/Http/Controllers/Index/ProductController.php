<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use App\Http\Logic\Index\IndexLogic;
use App\Http\Logic\Index\ProductLogic;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Predis\Client;

class ProductController extends Controller
{

    public function category(Request $request, $categoryId = 0)
    {
        // 1: 获取分类信息
        $data['productCategoryInfo'] = [];
        if (!empty($categoryId)) {
            $productCategoryInfo = ProductLogic::getProductCategoryInfo($categoryId);
            if (isset($productCategoryInfo['code']) && $productCategoryInfo['code'] == 0 && !empty($productCategoryInfo['data'])) {
                $data['productCategoryInfo'] = $productCategoryInfo['data'];
            }
        }

        // 2: 获取产品列表
        $page = $request->get('page', 1);
        $data['nowPage'] = $page;
        $data['pageCount'] = 1;
        $data['productList'] = [];
        $productList = ProductLogic::getProductList($categoryId, $page, 12);
        if (isset($productList['code']) && $productList['code'] == 0 && !empty($productList['data']['pageCount']) && !empty($productList['data']['productList'])) {
            $data['pageCount'] = $productList['data']['pageCount'];
            $data['productList'] = $productList['data']['productList'];
        }

        return view('index.product.category', $data);
    }

    public function product(Request $request, $productId = 0)
    {
        // 1: 获取产品信息
        $data['productInfo']  = [];
        $productInfo = ProductLogic::getProductDetail($productId);
        if (isset($productInfo['code']) && $productInfo['code'] == 0 && !empty($productInfo['data'])) {
            $data['productInfo'] = $productInfo['data'];
        } else {
            return view('404');
        }

        // 2: 获取分类信息
        $data['productCategoryInfo'] = [];
        $productCategoryInfo = ProductLogic::getProductCategoryInfo($data['productInfo']['category_id']);
        if (isset($productCategoryInfo['code']) && $productCategoryInfo['code'] == 0 && !empty($productCategoryInfo['data'])) {
            $data['productCategoryInfo'] = $productCategoryInfo['data'];
        }

        // 3: 获取上一个和下一个产品
        $data['nearProductList'] = [];
        $nearProductList = ProductLogic::getNearProductList($productId);
        if (isset($nearProductList['code']) && $nearProductList['code'] == 0 && !empty($nearProductList['data'])) {
            $data['nearProductList'] = $nearProductList['data'];
        }

        // 4: 获取相关产品
        $data['relateProductList'] = [];
        $relateProductList = ProductLogic::getRelateProductList($productId, $data['productInfo']['category_id']);
        if (isset($relateProductList['code']) && $relateProductList['code'] == 0 && !empty($relateProductList['data'])) {
            $data['relateProductList'] = $relateProductList['data'];
        }

        return view('index.product.product', $data);
    }
}
