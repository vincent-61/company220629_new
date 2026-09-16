@extends('wap.components.layout')

@section('content')

    <div class="content content_new">
        @if (!empty($recommendProductList))
            <!--产品展示-->
            <div class="more_i"><span>产品中心</span>
                <div class="more_i01"><a href="/wap/product.html" title="产品展示">更多</a></div>
                <div class="clear"></div>
            </div>
            <div class="p_i">
                @foreach ($recommendProductList as $recommendProduct)
                    <dl>
                        <a href="/wap/product/detail/{{ $recommendProduct['product_id'] }}.html">
                            <dt><img src="{{ $recommendProduct['img_190'] }}" height="120" alt=""></dt>
                            <dd>{{ $recommendProduct['product_name'] }}</dd>
                        </a>
                    </dl>
                @endforeach
                <div class="clear"></div>
            </div>
            <!--产品展示 end-->
        @endif

        <!--关于我们-->
        <div class="more_i"><span>关于我们</span>
            <div class="more_i01"><a href="/wap/about.html" title="关于我们">更多</a></div>
            <div class="clear"></div>
        </div>
        <div class="about_i">
            <dt><img src="{{ $platformInfo['about_img'] }}" alt="公司简介"></dt>
        </div>
        <div class="about_i">
            {!! $platformInfo['about'] !!}
        </div>
        <!--关于我们 end-->

        <!--电话-->
        <div class="dianhua">
            全国服务热线：
            <span>@if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif</span>
        </div>
        <!--电话-->

        @if (!empty($qualificationList1))
            <!--案例展示-->
            <div class="more_i"><span>优势品牌</span>
                <div class="clear"></div>
            </div>
            <div class="case_i">
                @foreach ($qualificationList1 as $qualification1)
                    <dl>
                        <dt><img src="{{ $qualification1['img_55'] }}" height="35"></dt>
                    </dl>
                @endforeach
                <div class="clear"></div>
            </div>
            <!--案例展示 end-->
        @endif

        @if (!empty($newsList))
            <!--新闻资讯-->
            <div class="more_i"><span>新闻中心</span>
                <div class="more_i01"><a href="/wap/news.html" title="更多">更多</a></div>
                <div class="clear"></div>
            </div>
            <div class="zixun_i">
                <ul>
                    @if (!empty($newsList))
                        @foreach ($newsList as $newsInfo)
                            <li>
                                <a href="/wap/news/detail/{{ $newsInfo['news_id'] }}.html">
                                    <div class="zixun_i01">></div>
                                    {{ $newsInfo['title'] }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
            <!--新闻资讯 end-->
        @endif

        @if (!empty($qualificationList2))
            <!--企业荣誉-->
            <div class="more_i"><span>资质认证</span>
                <div class="more_i01"><a href="caselist-13.html" title="更多">更多</a></div>
                <div class="clear"></div>
            </div>
            <div class="case_i">
                @foreach ($qualificationList2 as $qualification2)
                    <dl>
                        <dt><img src="{{ $qualification2['img_55'] }}" height="35"></dt>
                    </dl>
                @endforeach
                <div class="clear"></div>
            </div>
            <!--企业荣誉 end-->
        @endif

    </div>

@endsection
