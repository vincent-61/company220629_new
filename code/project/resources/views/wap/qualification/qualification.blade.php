@extends('wap.components.layout')

@section('content')

    <!--下边导航区域-->
    <div class="content">
        <div class="pro_bn">
            <ul class="list">
                @if (!empty($qualificationCategoryList))
                    @foreach ($qualificationCategoryList as $qualificationCategory)
                        <li><a href="/wap/qualification/list/{{ $qualificationCategory['category_id'] }}.html">{{ $qualificationCategory['category_name'] }}</a></li>
                    @endforeach
                @endif
            </ul>
        </div>
        <div class="about">
            <h1>{{ $qualificationInfo['name'] }}</h1>
            <center>
                <img src="{{ $qualificationInfo['img'] }}">
            </center>
            <span class="picContent"></span>

            @if (!empty($nearQualificationList['before']))
                <div class="page">上一条：<a href="/wap/qualification/detail/{{ $nearQualificationList['before']['qualification_id'] }}.html">{{ $nearQualificationList['before']['name'] }}</a></div>
            @else
                <div class="page">上一条：没有了！</div>
            @endif

            @if (!empty($nearQualificationList['after']))
                <div class="page">
                    <div class="page">下一条:<a href="/wap/qualification/detail/{{ $nearQualificationList['after']['qualification_id'] }}.html">{{ $nearQualificationList['after']['name'] }}</a></div>
                </div>
            @else
                <div class="page">下一条：没有了！</div>
            @endif

            <a href="/wap/qualification.html" title="返回" class="back">返回列表</a>

        </div>
    </div>

@endsection
