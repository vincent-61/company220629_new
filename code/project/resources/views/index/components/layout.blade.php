<!DOCTYPE html>
<html lang="zh-CN">
<!-- Mirrored from demo.cnfusu.com/c064/ by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 27 Sep 2021 11:25:26 GMT -->
<!-- Added by HTTrack -->
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<!-- /Added by HTTrack -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="Cache-Control" content="no-transform">
    <meta name="applicable-device" content="pc">
    <meta name="MobileOptimized" content="width">
    <meta name="HandheldFriendly" content="true">
    <title>@if(!empty($platformInfo['name'])) {{$platformInfo['name']}} @endif</title>
    @if(!empty($platformInfo['keywords']))<meta name="keywords" content="{{ $platformInfo['keywords'] }}">@endif
    @if(!empty($platformInfo['description']))<meta name="description" content="{{ $platformInfo['description'] }}">@endif
    <link rel="stylesheet" href="{{ $staticUrl }}css/css.css">

    <script type="text/javascript">

        try
        {
            var ua = navigator.userAgent;
            var ipad = ua.match(/(iPad).*OS\s([\d_]+)/),
                isIphone =  ua.match(/(iPhone\sOS)\s([\d_]+)/),
                isAndroid = ua.match(/(Android)\s+([\d.]+)/),
                isMobile = isIphone || isAndroid || ipad;
            //alert(ipad);
            if(isMobile) {
                location.href = '/wap/';}else{}
        }
        catch(e)
        {}

    </script>

</head>
<body>
<div class="header">
    <div class="top">
        <div class="container">
            <div class="fl">
                欢迎光临@if(!empty($platformInfo['name'])) {{$platformInfo['name']}} @endif网站，咨询热线@if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif @if(!empty($platformInfo['phone2']))&nbsp;/&nbsp;{{$platformInfo['phone2']}} @endif
            </div>
            <div class="fr"><a href="/about.html">公司简介</a> | <a href="/product.html">产品展示</a> | <a href="/contact.html">联系我们</a></div>
        </div>
    </div>
    <div id="logo">
        <div class="container">
            <div class="logo fl"><a href="" title=""><img src="@if(!empty($platformInfo['logo'])) {{$platformInfo['logo']}} @endif" alt=""></a></div>
            <div class="company fl">
                <h2>
                    专业电子元器件独立分销商
                </h2>
                <h3>
                    主营产品 : @if(!empty($platformInfo['main_product'])) {{$platformInfo['main_product']}} @endif
                </h3>
            </div>
            <div class="fr">
                <img src="@if(!empty($platformInfo['wechat_img'])) {{$platformInfo['wechat_img']}} @endif" width="200" style="margin-top: 25px;"></div>
            <div class="tel fr">
                <span><b style="color: black;">服务热线：</b>
                    <i>
                        @if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif
                        @if(!empty($platformInfo['phone2'])) <br />{{$platformInfo['phone2']}} @endif
                    </i>
                </span>
            </div>
        </div>
    </div>
</div>
<div class="nav">
    <div class="container">
        <ul>
            <li @if(in_array(Request::path(), ['/', 'index.html'])) class='on' @endif><a href="/">首页</a></li>
            <li @if(strpos(Request::path(), 'about') !== false) class='on' @endif><a href="/about.html">公司简介</a></li>
            <li @if(strpos(Request::path(), 'product') !== false) class='on' @endif><a href="/product.html">产品展示</a></li>
            <li @if(strpos(Request::path(), 'news') !== false) class='on' @endif><a href="/news.html">新闻中心</a></li>
            <li @if(strpos(Request::path(), 'qualification') !== false) class='on' @endif><a href="/qualification.html">资质认证</a></li>
            <li @if(strpos(Request::path(), 'contact') !== false) class='on' @endif><a href="/contact.html">联系我们</a></li>
            <li @if(strpos(Request::path(), 'message') !== false) class='on' @endif><a href="/message.html">在线留言</a></li>
        </ul>
    </div>
</div>

@yield('content')

<div class="links container">
    <span>友情链接：</span>
    <a href="https://www.sina.com.cn/" target="_blank">新浪</a>
    <a href="https://www.qq.com/" target="_blank">腾讯</a>
    <a href="https://www.360.cn/" target="_blank">360</a>
    <a href="https://www.baidu.com/" target="_blank">百度</a>
</div>
<div class="footer">
    <div class="container">
        <div class="fnav fl">
            <dl>
                <dt><a href="/about.html">关于我们</a></dt>
                <dd><a href="/about.html">公司介绍</a></dd>
                <dd><a href="/contact.html">联系我们</a></dd>
                <dd><a href="/qualification.html">资质认证</a></dd>
            </dl>
            <dl>
                <dt><a href="/product.html">产品展示</a></dt>
                @if (!empty($productCategoryList))
                    @foreach ($productCategoryList as $productCategory)
                        <dd><a href="/product/list/{{ $productCategory['category_id'] }}.html">{{ $productCategory['category_name'] }}</a></dd>
                    @endforeach
                @endif
            </dl>
            <dl>
                <dt><a href="/news.html">新闻中心</a></dt>
                @if (!empty($newsCategoryList))
                    @foreach ($newsCategoryList as $newsCategory)
                        <dd><a href="/news/list/{{ $newsCategory['category_id'] }}.html">{{ $newsCategory['category_name'] }}</a></dd>
                    @endforeach
                @endif
            </dl>
            <dl style="margin-right: 10px">
                <dt><a href="javascript:;">联系我们</a></dt>
                <dd>
                    <p>公司名称：@if(!empty($platformInfo['name'])) {{$platformInfo['name']}} @endif</p>
                    <p>销售经理：@if(!empty($platformInfo['contact_name'])) {{$platformInfo['contact_name']}} @endif</p>
                    <p>联系电话：@if(!empty($platformInfo['telephone'])) {{$platformInfo['telephone']}} @endif</p>
                    <p>
                        联系手机：@if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif
                        @if(!empty($platformInfo['phone2'])) &nbsp;/&nbsp;{{$platformInfo['phone2']}} @endif
                    </p>
                    <p>
                        QQ: @if(!empty($platformInfo['qq'])) {{$platformInfo['qq']}} @endif
                        @if(!empty($platformInfo['qq2'])) &nbsp;/&nbsp;{{$platformInfo['qq2']}} @endif
                    </p>
                    <p>邮箱：@if(!empty($platformInfo['email'])) {{$platformInfo['email']}} @endif</p>
                    <p>公司地址：@if(!empty($platformInfo['address'])) {{$platformInfo['address']}} @endif </p>
                    @if(!empty($platformInfo['address2'])) <p style="margin-left: 73px;"> {{$platformInfo['address2']}} </p> @endif
                </dd>
            </dl>
        </div>
        <div class="ewm fr">
            <h3>扫一扫关注微信</h3>
            <p><img src="@if(!empty($platformInfo['wechat_img'])) {{$platformInfo['wechat_img']}} @endif"></p>
        </div>
    </div>
</div>
<div class="copyright">
    <p>@if(!empty($platformInfo['name'])) {{$platformInfo['name']}} @endif, &nbsp;版权所有 <a href="https://beian.miit.gov.cn/" target="_blank">@if(!empty($platformInfo['copyright'])) {{$platformInfo['copyright']}} @endif</a></p>
</div>
<div class="kefu">
    <ul id="kefu">
        <li class="kefu-qq">
            <div class="kefu-tel-main">
                <div class="kefu-left"><i></i>
                    <p>QQ咨询 </p>
                </div>
                <div class="kefu-tel-right" style="margin-top: 5px; height: 40px;">
                    @if(!empty($platformInfo['qq']))<a href="http://wpa.qq.com/msgrd?v=3&uin={{$platformInfo['qq']}}&site=qq&menu=yes" target="_blank"> {{$platformInfo['qq']}} </a> @endif
                    @if(!empty($platformInfo['qq2']))<br /><a href="http://wpa.qq.com/msgrd?v=3&uin={{$platformInfo['qq2']}}&site=qq&menu=yes" target="_blank"> {{$platformInfo['qq2']}} </a> @endif
                </div>
            </div>
        </li>
        <li class="kefu-tel">
            <div class="kefu-tel-main">
                <div class="kefu-left"><i></i>
                    <p>联系电话 </p>
                </div>
                <div class="kefu-tel-right" style="margin-top: 5px; height: 40px;">
                    @if(!empty($platformInfo['phone']))<a href="tel:{{$platformInfo['phone']}} "> {{$platformInfo['phone']}} </a> @endif
                    @if(!empty($platformInfo['phone2']))<br /><a href="tel:{{$platformInfo['phone2']}} "> {{$platformInfo['phone2']}} </a> @endif
                </div>
            </div>
        </li>
        <li class="kefu-weixin">
            <div class="kefu-main">
                <div class="kefu-left"><i></i>
                    <p> 微信扫一扫 </p>
                </div>
                <div class="kefu-right"></div>
                <div class="kefu-weixin-pic"><img src="@if(!empty($platformInfo['wechat_img'])) {{$platformInfo['wechat_img']}} @endif"></div>
            </div>
        </li>
        <li class="kefu-ftop">
            <div class="kefu-main">
                <div class="kefu-left">
                    <a href="javascript:;"><i></i>
                        <p>返回顶部</p>
                    </a>
                </div>
                <div class="kefu-right"></div>
            </div>
        </li>
    </ul>
</div>
<script src="{{ $staticUrl }}js/jquery.min.js"></script>
<script src="{{ $staticUrl }}js/superslide.js"></script>
<script src="{{ $staticUrl }}js/js.js"></script>

</body>
</html>
