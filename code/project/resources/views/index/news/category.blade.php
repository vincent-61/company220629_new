@extends('index.components.layout')

@section('content')

    <!--点GO没有反应需要引用，放在PAGE.CSS上面-->
    <script src="{{ $staticUrl }}js/jquery.min.js" type='text/javascript'></script>
    <link href="{{ $staticUrl }}css/Page.css" rel="stylesheet" type="text/css">
    <script src="{{ $staticUrl }}js/page.js" type='text/javascript'></script>

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
                    <p>您的位置：<a href="/index.html">首页</a>>@if (!empty($newsCategoryInfo))<a href="">{{ $newsCategoryInfo['category_name'] }}</a> @else <a href="">新闻中心</a> @endif</p>
                </div>

                @if (!empty($newsList))

                <div class="list-1">
                    <dl>
                        @foreach ($newsList as $news)
                            <dt><a href="/news/detail/{{ $news['news_id'] }}.html">{{ $news['title'] }}</a><span>{{ $news['news_time'] }}</span></dt>
                            <dd> {{ $news['content'] }} </dd>
                        @endforeach
                    </dl>
                </div>
                <div style="text-align:center">
                    <div style="height:50px; font-size:12px; line-height:50px; text-align:left;zoom:1;display:inline-block;*display:inline;">
                        @if ($pageCount / $nowPage < 2)
                            @if ($nowPage == 1)
                                <div class="CantPage" style="margin-top:9px;">首页</div>
                            @else
                                <a href="{{ url()->current() }}">
                                    <div class="CanPage" style="margin-top:9px;">首页</div>
                                </a>
                            @endif
                        @endif

                        @if ($nowPage == 1)
                            <div class="CantPage" style="margin-top:9px;">上一页</div>
                        @else
                            <a href="{{ url()->current() }}?page={{ $nowPage - 1 }}">
                                <div class="CanPage" style="margin-top:9px;">上一页</div>
                            </a>
                        @endif

                        @for ($i = 1; $i <= $pageCount; $i++)
                            @if ($i == $nowPage)
                                <div class="PageCantSelect" style="margin-top:9px;">{{ $i }}</div>
                            @elseif ($i == $nowPage - 1 || $i == $nowPage + 1)
                                <a href="{{ url()->current() }}?page={{ $i }}">
                                    <div class="PageSelect" style="margin-top:9px;">{{ $i }}</div>
                                </a>
                            @elseif ($i == 1 || $i == $pageCount)
                                <a href="{{ url()->current() }}?page={{ $i }}">
                                    <div class="PageSelect" style="margin-top:9px;">{{ $i }}</div>
                                </a>
                            @elseif ($i == $nowPage - 2 || $i == $nowPage + 2)
                                <div style="float:left; width:32px; height:32px;margin-left:9px; text-align:center;line-height:32px;">...</div>
                            @endif
                        @endfor

                        @if ($nowPage == $pageCount)
                            <div class="CantPage" style="margin-top:9px;">下一页</div>
                        @else
                            <a href="{{ url()->current() }}?page={{ $nowPage + 1 }}">
                                <div class="CanPage" style="margin-top:9px;">下一页</div>
                            </a>
                        @endif

                        @if ($pageCount / $nowPage > 2)
                            @if ($nowPage == $pageCount)
                                <div class="CantPage" style="margin-top:9px;">尾页</div>
                            @else
                                <a href="{{ url()->current() }}?page={{ $pageCount }}">
                                    <div class="CanPage" style="margin-top:9px;">尾页</div>
                                </a>
                            @endif
                        @endif

                        <div style="float:left; height:32px;margin-left:20px; text-align:center;">共 <span class="MaxPage">{{ $pageCount }}</span> 页</div>
                        <div style="float:left; height:32px;margin-left:20px; text-align:center;">第 <input type="text" class="PageInput" style="vertical-align:middle"> 页</div>
                        <a class="GoClass" style="margin-top:12px;">GO</a>
                    </div>
                </div>

                @endif
            </div>
        </div>
    </div>

@endsection
