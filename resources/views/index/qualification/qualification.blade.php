@extends('index.components.layout')

@section('content')

    <div class="ban" style="background:url({{ $staticUrl }}image/1589001600538534.jpg) no-repeat top center;"></div>
    <div class="ny">
        <div class="container">
            <div class="fl">
                <div class="title"><span>资质认证</span>list center</div>
                <div class="category clearfix">
                    @if (!empty($qualificationCategoryList))
                        @foreach ($qualificationCategoryList as $qualificationCategory)
                            <h3 @if (!empty($qualificationCategoryInfo) && $qualificationCategoryInfo['category_id'] == $qualificationCategory['category_id']) class="on" @endif>
                                <span class="iconfont icon-jiantou"></span><a href="/qualification/list/{{ $qualificationCategory['category_id'] }}.html">{{ $qualificationCategory['category_name'] }}</a>
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
                <div class="title"><span>@if (!empty($qualificationCategoryInfo)){{ $qualificationCategoryInfo['category_name'] }} @else 资质认证 @endif</span>
                    <p>您的位置：<a href="index.html">首页</a>>@if (!empty($qualificationCategoryInfo))<a href="/qualification/list/{{ $qualificationCategoryInfo['category_id'] }}.html">{{ $qualificationCategoryInfo['category_name'] }}</a> @endif</p>
                </div>
                <div class="news">
                    <h1>{{ $qualificationInfo['name'] }}</h1>
                    <div class="info">
                        <p align="center" style="padding-top:20px;"><img src="{{ $qualificationInfo['img'] }}" border="0"/></p>
                        <p><br></p>
                        <p><br></p>
                        <p><br></p>
                    </div>
                </div>

                <div class="page">

                    @if (!empty($nearQualificationList['before']))
                        上一条：<a href="/qualification/detail/{{ $nearQualificationList['before']['qualification_id'] }}.html">{{ $nearQualificationList['before']['name'] }}</a>
                    @else
                        上一条：没有了！
                    @endif

                    @if (!empty($nearQualificationList['after']))
                        <span>
                            下一条：<a href="/qualification/detail/{{ $nearQualificationList['after']['qualification_id'] }}.html">{{ $nearQualificationList['after']['name'] }}</a>
                        </span>
                    @else
                        <span>下一条：没有了！</span>
                    @endif

                </div>
                <div class="xg-pro">
                    <div class="tit"><span>相关推荐</span></div>
                    <ul>
                        @if (!empty($relateQualificationList))
                            @foreach ($relateQualificationList as $relateQualification)
                                <li>
                                    <a href="/qualification/detail/{{ $relateQualification['qualification_id'] }}.html"><img src="{{ $relateQualification['img'] }}" onerror="javascript:this.src='{{ $relateQualification["img"] }}';" >
                                        <p>{{ $relateQualification['name'] }} </p>
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
