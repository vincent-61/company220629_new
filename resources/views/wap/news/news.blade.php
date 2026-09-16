@extends('wap.components.layout')

@section('content')

    <!--下边导航区域-->
    <div class="content">
        <div class="pro_bn">
            <ul class="list">
                @if (!empty($newsCategoryList))
                    @foreach ($newsCategoryList as $newsCategory)
                        <li><a href="/wap/news/list/{{ $newsCategory['category_id'] }}.html">{{ $newsCategory['category_name'] }}</a></li>
                    @endforeach
                @endif
            </ul>
        </div>
        <div class="about">
            <h1>{{ $newsInfo['title'] }}</h1>
            <center>
                {{ $newsInfo['news_time'] }}
            </center>
            <span class="picContent">
                {!! $newsInfo['content'] !!}
                <p><br></p>
            </span>

            @if (!empty($nearNewsList['before']))
                <div class="page">上一条：<a href="/wap/news/detail/{{ $nearNewsList['before']['news_id'] }}.html">{{ $nearNewsList['before']['titleFormat'] }}</a></div>
            @else
                <div class="page">上一条：没有了！</div>
            @endif

            @if (!empty($nearNewsList['after']))
                <div class="page">
                    <div class="page">下一条:<a href="/wap/news/detail/{{ $nearNewsList['after']['news_id'] }}.html">{{ $nearNewsList['after']['titleFormat'] }}</a></div>
                </div>
            @else
                <div class="page">下一条：没有了！</div>
            @endif

            <a href="/wap/news.html" title="返回" class="back">返回列表</a>

        </div>


    </div>

@endsection
