@extends('index.components.layout')

@section('content')
    @if (!empty($bannerList))
        <div class="banner">
            <div class="hd">
                <ul>
                    @foreach ($bannerList as $key => $banner)
                        <li class="@if($key == 0) on @endif"></li>
                    @endforeach
                </ul>
            </div>
            <div class="bd">
                <ul>
                    @foreach ($bannerList as $banner)
                        <li style="background: url({{ $banner['img'] }}) 50% 0% no-repeat;"><a href="#" title=""></a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="index-pro">
        <div class="container">
            <div class="fl">
                <div class="title"><span>产品中心</span>PORDUCT CENTER</div>
                <div class="category1 clearfix">
                    <ul>
                        @if (!empty($productCategoryList))
                            @foreach ($productCategoryList as $productCategory)
                                <li class="sortli"><a href="/product/list/{{ $productCategory['category_id'] }}.html"> <img src="{{ $productCategory['img_50'] }}">{{ $productCategory['category_name'] }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                    <style>
                        .sortli {
                            width: 100%;
                            border-bottom: 1px solid #ddd;
                            height: 75px;
                            line-height: 75px;
                            float: left;
                            overflow: hidden;
                            background: #fff;
                            font-size: 16px;
                            display: inline;
                        }

                        .sortli a {
                            width: 100%;
                            float: left;
                        }

                        .sortli img {
                            width: 70px;
                            height: 50px;
                            margin-right: 14px;
                            float: left;
                            margin-top: 12px;
                            margin-left: 20px;
                        }
                    </style>
                </div>
                <div class="contact">
                    <p>全国服务热线</p>
                    <span>
                        @if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif
                        @if(!empty($platformInfo['phone2'])) <br />{{$platformInfo['phone2']}} @endif
                    </span>
                    <div class="more" style="margin-top: 2px;"><a href="tel:{{$platformInfo['phone']}} " target="_blank">立即咨询</a></div>
                </div>
            </div>
            @if (!empty($recommendProductList))
                <div class="fr">
                    <div class="title"><span>热门产品</span>
                        <p><a href="/product.html">MORE+</a></p>
                    </div>
                    <div class="content">
                        <ul>
                            @foreach ($recommendProductList as $recommendProduct)
                                <li>
                                    <a href="/product/detail/{{ $recommendProduct['product_id'] }}.html" title="{{ $recommendProduct['product_name'] }}">
                                        <div class="img"><img src="{{ $recommendProduct['img_190'] }}" alt="{{ $recommendProduct['product_name'] }}"></div>
                                        <p>{{ $recommendProduct['product_name'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (!empty($qualificationList1))
        <div style="background:#ECECEC; ">
            <div align="center"><img src="{{ $staticUrl }}picture/yarr.png"></div>
            <div style="height:30px;"></div>
            <div style="background: url({{ $staticUrl }}image/linebg.png)center center no-repeat;height: 70px;">
                <div style="font-size: 36px; color: #423D8E;font-weight: 600; text-align: center;letter-spacing:6px;">优势品牌</div>
                <div style="font-size: 16px; color: #423D8E;font-weight: 300; text-align: center;letter-spacing:1px;">ADVANTAGE BRAND</div>
            </div>
            <div style="height:30px;"></div>
            <div class="container1">
                @foreach ($qualificationList1 as $qualification1)
                    <div class="col-md-2 col-sm-4 col-xs-4 col-mb-12" style="padding:2px; ">
                        <div align="center" style="background:#FFFFFF;border-radius:0px; text-align:center; overflow:hidden; "><img src="{{ $qualification1['img_55'] }}" border="0"></div>
                    </div>
                @endforeach
            </div>
            <div style="height:50px;clear:both"></div>
        </div>
    @endif

{{--@if (!empty($platformInfo['video']) && !empty($platformInfo['video_poster']))--}}
{{--<div class="index-case">--}}
{{--    <div class="container">--}}
{{--        <div class="sec-title"><span>厂家介绍</span>--}}
{{--            <p>欢迎各地新老客户前来洽谈合作，共同创造美好明天！</p>--}}
{{--        </div>--}}
{{--        <div class="content">--}}
{{--            --}}{{--<ul>--}}
{{--            --}}{{--    @if (!empty($qualificationList2))--}}
{{--            --}}{{--        @foreach ($qualificationList2 as $qualification2)--}}
{{--            --}}{{--            <li><img src="{{ $qualification2['img_190'] }}" ></li>--}}
{{--            --}}{{--        @endforeach--}}
{{--            --}}{{--    @endif--}}
{{--            --}}{{--</ul>--}}
{{--            --}}{{--<a class="prev" href="javascript:void(0)"></a> <a class="next" href="javascript:void(0)"></a>--}}

{{--            <video width="80%" id="videoShow" src="{{ $platformInfo['video'] }}" poster="{{ $platformInfo['video_poster'] }}" undefined="" controls="controls">您的浏览器不支持video播放</video>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--@endif--}}
<div class="index-about">
    <div class="container">
        <div class="content">
            <div class="fl">
                <img style="height: 324px;" src="{{ $platformInfo['about_img'] }}" alt="关于我们">
                {{--<video width="100%" id="videoShow" src="{{ $platformInfo['about_video'] }}" poster="{{ $platformInfo['about_img'] }}" undefined="" controls="controls">您的浏览器不支持video播放</video>--}}
            </div>
            <div class="fr">
                <h2><span>ABOUT US</span>关于我们</h2>
                {!! $platformInfo['about'] !!}
            </div>
        </div>
    </div>
</div>

<div class="index-news">
    <div class="container">

        @if (!empty($newsList1))
            <div class="fl">
                <div class="title"><i><img src="{{ $staticUrl }}picture/news-1.png"></i><span>公司动态</span><em><a href="/news/list/{{ $newsList1['newsCategoryInfo']['category_id'] }}.html">MORE+</a></em></div>
                <div class="content">
                @if (!empty($newsList1['newsList']))
                    @foreach ($newsList1['newsList'] as $newsInfo)
                        <h3><a href="/news/detail/{{ $newsInfo['news_id'] }}.html">{{ $newsInfo['title_format'] }}</a><span>{{ $newsInfo['news_time'] }}</span></h3>
                        <dl>
                            <dt><a href="/news/detail/{{ $newsInfo['news_id'] }}.html"><img src="{{ $newsInfo['img_160'] }}"></a></dt>
                            <dd>
                                <h4><a href="/news/detail/{{ $newsInfo['news_id'] }}.html">{{ $newsInfo['title'] }}</a></h4>
                                <p>{{ $newsInfo['content'] }}</p>
                            </dd>
                        </dl>
                    @endforeach
                @endif
                </div>
            </div>
        @endif

        @if (!empty($newsList2))
            <div class="fr">
                <div class="title"><i><img src="{{ $staticUrl }}picture/news-2.png"></i> <span>行业动态</span><em><a href="/news/list/{{ $newsList2['newsCategoryInfo']['category_id'] }}.html">MORE+</a></em></div>
                <div class="content">
                    @if (!empty($newsList2['newsList']))
                        @foreach ($newsList2['newsList'] as $newsInfo)
                            <h3><a href="/news/detail/{{ $newsInfo['news_id'] }}.html">{{ $newsInfo['title_format'] }}</a><span>{{ $newsInfo['news_time'] }}</span></h3>
                            <dl>
                                <dt><a href="/news/detail/{{ $newsInfo['news_id'] }}.html"><img src="{{ $newsInfo['img_160'] }}"></a></dt>
                                <dd>
                                    <h4><a href="/news/detail/{{ $newsInfo['news_id'] }}.html">{{ $newsInfo['title'] }}</a></h4>
                                    <p>{{ $newsInfo['content'] }}</p>
                                </dd>
                            </dl>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>


@endsection
