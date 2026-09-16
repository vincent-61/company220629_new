<!DOCTYPE html>
<html class="ui-mobile" xmlns="http://www.w3.org/1999/xhtml">
<!-- Added by HTTrack -->
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<!-- /Added by HTTrack -->
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <meta name="viewport" content="width=320, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>@if(!empty($platformInfo['name'])) {{$platformInfo['name']}} @endif</title>
    @if(!empty($platformInfo['keywords']))<meta name="keywords" content="{{ $platformInfo['keywords'] }}"> @endif
    @if(!empty($platformInfo['description']))<meta name="description" content="{{ $platformInfo['description'] }}"> @endif
    <link rel="stylesheet" type="text/css" href="{{ $staticWapUrl }}css/subpage.css">
    <link rel="stylesheet" type="text/css" href="{{ $staticWapUrl }}css/master.css">
    <link rel="stylesheet" type="text/css" href="{{ $staticWapUrl }}css/swiper.css">
    <script type="text/javascript" src="{{ $staticWapUrl }}js/jquery.js"></script>
    <script type="text/javascript" src="{{ $staticWapUrl }}js/nav.js"></script>
    <script src="{{ $staticWapUrl }}js/responsiveslides.min.js"></script>
    <script src="{{ $staticWapUrl }}js/slide.js"></script>
</head>
<body>
<style>
    .head {
        background: #ffffff;
    }

    .foot {
        background: #0d77e5;
    }

    .nav {
        background: #0d77e5 !important
    }

    .nav_color {
        background: #222;
    }
</style>
<ul class="nav">
    <li><a href="/wap/index.html" title="首页"><span class="iconfont">&#xe607;</span>首页</a></li>
    <li><a href="/wap/about.html" title="公司简介">公司简介</a></li>
    <li><a href="/wap/product.html" title="产品展示">产品展示</a></li>
    <li><a href="/wap/news.html" title="新闻中心">新闻中心</a></li>
    <li><a href="/wap/qualification.html" title="资质认证">资质认证</a></li>
    <li><a href="/wap/contact.html" title="联系我们">联系我们</a></li>
</ul>
<div class="allpage">
    <div class="black-fixed iconfont">&#xe60f;</div>
    <!--header-->
    <div class="header">
        <div class="head"><a href="/wap/index.html" class="logo"><img src="@if(!empty($platformInfo['logo'])) {{$platformInfo['logo']}} @endif" height="40px;"></a>
            <div class="nav-btn iconfont">&#xe605;</div>
        </div>
    </div>
    <!--header end-->

    @if (!empty($bannerList))
        <div class="slide_container">
            <ul class="rslides" id="slider">
                @foreach ($bannerList as $key => $banner)
                    <li><img src="{{ $banner['img'] }}" alt=""></li>
                @endforeach
            </ul>
        </div>
    @endif

    <!--下边导航区域-->
    <div class="nav_new tpp">
        <ul>
            <li class="blue"><a href="/wap/index.html">网站首页</a></li>
            <li class="blue"><a href="/wap/about.html">公司简介</a></li>
            <li class="blue"><a href="/wap/product.html">产品展示</a></li>
        </ul>
        <div class="clear"></div>
    </div>
    <div class="nav_new boo">
        <ul>
            <li class="blue"><a href="/wap/news.html">新闻中心</a></li>
            <li class="blue"><a href="/wap/qualification.html">资质认证</a></li>
            <li class="blue"><a href="/wap/contact.html">联系我们</a></li>
        </ul>
        <div class="clear"></div>
    </div>
    <!--下边导航区域-->

    @yield('content')

    <div class="footer">
        <div class="foot foot-relative" id="foot">
            <div class="foot-relative">
                <a href="/wap/index.html" title="首页">
                    <span class="commonfont">&#xe608;</span>
                    <h3>首页</h3>
                </a>
                <a href="tel:@if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif" title="电话">
                    <span class="commonfont">&#xe604;</span>
                    <h3>电话</h3>
                </a>
                <a href="sms:@if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif" title="短信">
                    <span class="commonfont">&#xe601;</span>
                    <h3>短信</h3>
                </a>
                <a href="/wap/contact.html" title="联系">
                    <span class="commonfont">&#xe603;</span>
                    <h3>联系</h3>
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
