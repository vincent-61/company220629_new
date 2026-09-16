@extends('wap.components.layout')

@section('content')

    <link href="{{ $staticWapUrl }}css/Page.css" rel="stylesheet" type="text/css">
    <script src="{{ $staticWapUrl }}js/page.js" type='text/javascript'></script>
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
        <ul class="common_news" id="contentArea">
            @if (!empty($newsList))
                @foreach ($newsList as $news)
                    <li class="news1">
                        <a href="/wap/news/detail/{{ $news['news_id'] }}.html">
                            <span>{{ $news['title'] }}</span>
                            <span>{{ $news['news_time'] }}</span>
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
        <div style="text-align:center">
            <div style="height:50px; font-size:12px; line-height:50px; text-align:left;zoom:1;display:inline-block;*display:inline;">
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
            </div>
        </div>
    </div>

@endsection
