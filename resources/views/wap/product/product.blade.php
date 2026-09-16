@extends('wap.components.layout')

@section('content')

    <!--下边导航区域-->
    <div class="content">
        <div class="pro_bn">
            <ul class="list">
                @if (!empty($productCategoryList))
                    @foreach ($productCategoryList as $productCategory)
                        <li><a href="/wap/product/list/{{ $productCategory['category_id'] }}.html">{{ $productCategory['category_name'] }}</a></li>
                    @endforeach
                @endif
            </ul>
        </div>
        <div class="about">
            <h1>{{ $productInfo['product_name'] }}</h1>
            <center>
                <img src="{{ $productInfo['img'] }}">
            </center>
            <span class="picContent">
                {!! $productInfo['content'] !!}
            </span>

            @if (!empty($nearProductList['before']))
                <div class="page">上一个产品：<a href="/wap/product/detail/{{ $nearProductList['before']['product_id'] }}.html">{{ $nearProductList['before']['product_name'] }}</a></div>
            @else
                <div class="page">上一个产品：没有了！</div>
            @endif

            @if (!empty($nearProductList['after']))
                <div class="page">
                    <div class="page">下一个产品:<a href="/wap/product/detail/{{ $nearProductList['after']['product_id'] }}.html">{{ $nearProductList['after']['product_name'] }}</a></div>
                </div>
            @else
                <div class="page">下一个产品：没有了！</div>
            @endif

            <a href="/wap/product.html" title="返回" class="back">返回列表</a>

        </div>
    </div>

@endsection
