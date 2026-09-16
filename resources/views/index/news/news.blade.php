@extends('index.components.layout')

@section('content')

    <div class="ban" style="background:url({{ $staticUrl }}image/1589001587721081.jpg) no-repeat top center;"></div>
    <div class="ny">
        <div class="container">
            <div class="fl">
                <div class="title"><span>新闻中心</span>list center</div>
                <div class="category clearfix">
                    @if (!empty($newsCategoryList))
                        @foreach ($newsCategoryList as $newsCategory)
                            <h3 @if (!empty($newsCategoryInfo) && $newsCategoryInfo['category_id'] == $newsCategory['category_id']) class="on" @endif>
                                <span class="iconfont icon-jiantou"></span><a href="/news/list/{{ $newsCategory['category_id'] }}.html">{{ $newsCategory['category_name'] }}</a>
                            </h3>
                        @endforeach
                    @endif
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
            <div class="fr">
                <div class="title"><span>@if (!empty($newsCategoryInfo)){{ $newsCategoryInfo['category_name'] }} @else 新闻中心 @endif</span>
                    <p>您的位置：<a href="index.html">首页</a>>@if (!empty($newsCategoryInfo))<a href="/news/list/{{ $newsCategoryInfo['category_id'] }}.html">{{ $newsCategoryInfo['category_name'] }}</a> @endif</p>
                </div>
                <div class="news">
                    <h1>{{ $newsInfo['title'] }}</h1>
                    <div class="date"><span>作者：{{ $newsInfo['author'] }}</span> <span>发布时间：{{ $newsInfo['news_time'] }}</span></div>
                    <div class="info">
                        {!! $newsInfo['content'] !!}
                        <p><br></p>
                        <p><br></p>
                        <p><br></p>
                    </div>
                </div>
                <div class="page">

                    @if (!empty($nearNewsList['before']))
                        上一条：<a href="/news/detail/{{ $nearNewsList['before']['news_id'] }}.html">{{ $nearNewsList['before']['titleFormat'] }}</a>
                    @else
                        上一条：没有了！
                    @endif

                    @if (!empty($nearNewsList['after']))
                        <span>
                            下一条：<a href="/news/detail/{{ $nearNewsList['after']['news_id'] }}.html">{{ $nearNewsList['after']['titleFormat'] }}</a>
                        </span>
                    @else
                        <span>下一条：没有了！</span>
                    @endif

                </div>
                <div class="xg-news">
                    <div class="tit"><span>相关资讯</span></div>
                    <ul>
                        @if (!empty($relateNewsList))
                            @foreach ($relateNewsList as $relateNews)
                                <li>
                                    <a href="/news/detail/{{ $relateNews['news_id'] }}.html"> {{ $relateNews['titleFormat'] }}</a><span>{{ $relateNews['news_time'] }}</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
