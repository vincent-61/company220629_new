@extends('index.components.layout')

@section('content')

    <div class="ban" style="background:url({{ $staticUrl }}image/1589001488755051.jpg) no-repeat top center;"></div>
    <div class="ny">
        <div class="container">
            <div class="fl">
                <div class="title"><span>公司简介</span>about us</div>
                <div class="category clearfix">
                    <h3 class="on"><span class="iconfont icon-jiantou"></span><a href="">公司介绍</a></h3>
                    <h3><span class="iconfont icon-jiantou"></span><a href="/contact.html">联系我们</a></h3>
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
                <div class="title"><span>公司简介</span>
                    <p>您的位置：<a href="/">首页</a>><a href="/about.html">公司介绍</a></p>
                </div>
                <div class="news">
                    <div class="info">
                        <p style="widows:1;text-transform:none;text-indent:0px;margin:10px 0px;letter-spacing:normal;font:14px/24px sans-serif;color:#000000;word-spacing:0px;-webkit-text-stroke-width:0px">@if(!empty($platformInfo['introduce'])) {!! $platformInfo['introduce'] !!} @endif</p>
                        <p><br class="Apple-interchange-newline"></p>
                        <p><br></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
