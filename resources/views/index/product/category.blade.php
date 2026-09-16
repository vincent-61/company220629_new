@extends('index.components.layout')

@section('content')

    <!--点GO没有反应需要引用，放在PAGE.CSS上面-->
    <script src="{{ $staticUrl }}js/jquery.min.js" type='text/javascript'></script>

    <link href="{{ $staticUrl }}css/Page.css" rel="stylesheet" type="text/css">
    <script src="{{ $staticUrl }}js/page.js" type='text/javascript'></script>

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
                                            <img src="{{ $productCategory['img_50'] }}">{{ $productCategory['category_name'] }}
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
                    <p>您的位置：<a href="index.html">首页</a>>@if (!empty($productCategoryInfo))<a href="/product.html">产品展示</a>> <a href="">{{ $productCategoryInfo['category_name'] }}</a> @else <a href="">产品展示</a> @endif</p>
                </div>

                @if (!empty($productList))

                    <div class="list-2">
                        <ul>
                            @foreach ($productList as $product)
                                <li>
                                    <a href="/product/detail/{{ $product['product_id'] }}.html"><img src="{{ $product['img_190'] }}" alt="{{ $product['product_name'] }}">
                                        <p>{{ $product['product_name'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
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
