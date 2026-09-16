@extends('index.components.layout')

@section('content')

    <div class="ban" style="background:url({{ $staticUrl }}image/1589001488755051.jpg) no-repeat top center;"></div>
    <div class="ny">
        <div class="container">
            <div class="fl">
                <div class="title"><span>在线留言</span>message</div>
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
                <div class="title"><span>在线留言</span>
                    <p>您的位置：<a href="/index.html">首页</a>><a href="">在线留言</a></p>
                </div>
                <div class="news">
                    <div class="info">
                        <p>
                        <div class="message-page">
                            <form name="form" id="form">
                                <div>
                                    <div class="row-1 ico-name">
                                        <input name="name" type="text" class="txt" value="" placeholder="您的称呼" data-required="name" error="请输入您的称呼" maxlength="5">
                                        <i class="tip">*</i></div>
                                    <div class="row-1 ico-phone">
                                        <input name="phone" type="text" class="txt" value="" placeholder="联系电话" data-required="phone" error="请输入正确的联系电话" maxlength="15">
                                        <i class="tip">*</i></div>
                                    <div class="row-1 ico-email">
                                        <input name="email" type="text" class="txt" value="" placeholder="邮箱地址" data-required="email" error="请输入正确的邮箱地址" maxlength="50">
                                        <i class="tip">*</i></div>
                                    <div class="row-2">
                                        <textarea class="text" name="content" placeholder="请填写您的需求信息..."></textarea>
                                    </div>
                                    <div class="row-3">
                                        <input type="text" class="code" name="captcha" value="" placeholder="验证码" data-required="required" null="请输入验证码" maxlength="4">
                                        <img id="captchaPic" src="{{ captcha_src('flat') }}" onclick="this.src='{{captcha_src('flat')}}'+Math.random()">
                                    </div>
                                    <div class="row-3">
                                        <input type="button" class="submit" value="提交" id="messageButton">
                                        <input type="reset" class="reset" value="重置">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ $staticUrl }}js/jquery.min.js" type='text/javascript'></script>
    <script type="text/javascript">
        $(function() {
            $('#messageButton').on("click", function() {
                $.ajax({
                    url: '/sendMessage?_token={{ csrf_token() }}',
                    type: 'POST',
                    dataType: 'json',
                    data: $("#form").serialize(),
                    success: function (res) {
                        alert(res.message);
                        if (res.code !== 0) {
                            $("#captchaPic").attr("src", "{{ captcha_src('flat') }}" + Math.random());
                        } else {
                            location.reload();
                        }
                    }
                })
            });
        });
    </script>

@endsection
