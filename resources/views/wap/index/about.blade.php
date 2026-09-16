@extends('wap.components.layout')

@section('content')

    <!--下边导航区域-->
    <div class="content">
        <div class="about">
            @if(!empty($platformInfo['introduce'])) {!! $platformInfo['introduce'] !!} @endif

            <p><br class="Apple-interchange-newline"></p><p><br></p>
        </div>
    </div>

@endsection
