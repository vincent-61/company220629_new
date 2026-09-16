@extends('index.components.layout')

@section('content')

    <script src="{{ $staticUrl }}js/jquery.min.js" type='text/javascript'></script>

    <div class="ban" style="background:url({{ $staticUrl }}image/1589001558409289.jpg) no-repeat top center;"></div>
    <div class="ny">
        <div class="container">
            <div class="fl">
                <div class="title"><span>产品展示</span>list center</div>
                <div class="category1 clearfix">
                    <ul class="sortul">
                        @if (!empty($productCategoryList))
                            @foreach ($productCategoryList as $productCategory)
                                <li class="sortli">
                                    <div class="sorta">
                                        <a href="/product/list/{{ $productCategory['category_id'] }}.html">
                                            <img src="{{ $productCategory['img'] }}">{{ $productCategory['category_name'] }}
                                        </a>
                                    </div>
                                    <div style="clear:both"></div>
                                    <ul class="nsortliul" style="display:none;">
                                        {{--<li class="nsortli"><a href="ProductList16_2_1.html">ALTERA</a></li>--}}
                                    </ul>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
                <script>
                    /* Slide Toogle */
                    $("ul.sortul li > div.sorta").click(function () {
                        $(this).parent().find("ul.nsortliul").slideToggle();
                    });

                </script>
                <style>
                    .sortli {
                        width: 100%;
                        line-height: 75px;
                        float: left;
                        overflow: hidden;
                        background: #fff;
                        font-size: 16px;
                        display: inline;
                    }

                    .sortli .sorta {
                        width: 100%;
                        border-bottom: 1px solid #ddd;
                    }

                    .sortli img {
                        width: 70px;
                        height: 50px;
                        margin-right: 14px;
                        float: left;
                        margin-top: 12px;
                        margin-left: 20px;
                    }

                    .nsortli {
                        display: block;
                        border-bottom: 1px solid #e1e1e1;
                        width: 100%;
                        line-height: 57px;
                        background-size: 5px;
                    }

                    .nsortli a {
                        display: block;
                        color: #666;
                        font-size: 14px;
                        padding-left: 60px;
                        background: url({{ $staticUrl }}image/menu01.jpg) no-repeat;
                    }
                </style>
                <div class="contact">
                    <p>全国服务热线</p>
                    <span>
                        @if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif
                        @if(!empty($platformInfo['phone2'])) <br />{{$platformInfo['phone2']}} @endif
                    </span>
                    <div class="more" style="margin-top: 2px;"><a href="tel:{{$platformInfo['phone']}} " target="_blank">立即咨询</a></div>
                </div>
            </div>
            <div class="fr">
                <div class="title"><span>产品展示</span>
                    <p>您的位置：<a href="/">首页</a>><a href="/product.html">产品展示</a>> <a href="/product/list/{{ $productCategoryInfo['category_id'] }}.html">{{ $productCategoryInfo['category_name'] }}</a></p>
                </div>
                <div class="pro">
                    <div class="img">
                        <div class="pc-slide">
                            <div class="view">
                                <div class="swiper-container swiper-container-horizontal">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide img-center">
                                            <div class="imgauto"><img src="{{ $productInfo['img'] }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text">
                        <h1 class="protit">{{ $productInfo['product_name'] }}</h1>
                        <div class="desc">
                            {{ $productInfo['desc'] }}
                        </div>
                        <a href="/message.html" class="btn"><span>在线订购</span></a>
                    </div>
                </div>
                <div class="pro-content">
                    <div class="tits"><span>产品详情</span></div>
                    <div class="info">
                        {!! $productInfo['content'] !!}
                    </div>
                </div>
                <div class="page">

                    @if (!empty($nearProductList['before']))
                        上一个：<a href="/product/detail/{{ $nearProductList['before']['product_id'] }}.html">{{ $nearProductList['before']['product_name'] }}</a>
                    @else
                        上一个：没有了！
                    @endif

                    @if (!empty($nearProductList['after']))
                        <span>
                            下一个：<a href="/product/detail/{{ $nearProductList['after']['product_id'] }}.html">{{ $nearProductList['after']['product_name'] }}</a>
                        </span>
                    @else
                        <span>下一个：没有了！</span>
                    @endif

                </div>
                <div class="xg-pro">
                    <div class="tit"><span>相关产品</span></div>
                    <ul>
                        @if (!empty($relateProductList))
                            @foreach ($relateProductList as $relateProduct)
                                <li>
                                    <a href="/product/detail/{{ $relateProduct['product_id'] }}.html"><img src="{{ $relateProduct['img'] }}" onerror="javascript:this.src='{{ $relateProduct["img"] }}';">
                                        <p>{{ $relateProduct['product_name'] }} </p>
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
