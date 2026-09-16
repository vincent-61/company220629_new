@extends('wap.components.layout')

@section('content')

    <link href="{{ $staticWapUrl }}css/Page.css" rel="stylesheet" type="text/css">
    <script src="{{ $staticWapUrl }}js/page.js" type='text/javascript'></script>

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
        <div class="common_news" id="contentArea">
            <ul class="photo-list">
                @if (!empty($productList))
                    @foreach ($productList as $product)
                        <li class="news4">
                            <a href="/wap/product/detail/{{ $product['product_id'] }}.html">
                                <img src="{{ $product['img_190'] }}" width="257" height="150"><span>{{ $product['product_name'] }} </span>
                            </a>
                        </li>
                    @endforeach

                    @if( count($productList) % 2 != 0)
                        <li class="news4"><div style="height: 190px">&nbsp;</div></li>
                    @endif
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
