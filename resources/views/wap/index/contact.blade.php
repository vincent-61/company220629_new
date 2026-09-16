@extends('wap.components.layout')

@section('content')

    <!--下边导航区域-->
    <div class="content">
        <div class="about">
            <p>公司名称：@if(!empty($platformInfo['name'])) {{$platformInfo['name']}} @endif<br></p>
            <p>销售经理：@if(!empty($platformInfo['contact_name'])) {{$platformInfo['contact_name']}} @endif</p>
            <p>联系座机：@if(!empty($platformInfo['telephone'])) {{$platformInfo['telephone']}} @endif</p>
            <p>联系电话：@if(!empty($platformInfo['phone'])) {{$platformInfo['phone']}} @endif @if(!empty($platformInfo['phone2']))&nbsp;/&nbsp;{{$platformInfo['phone2']}} @endif</p>
            <p>QQ: @if(!empty($platformInfo['qq'])) {{$platformInfo['qq']}} @endif @if(!empty($platformInfo['qq2']))&nbsp;/&nbsp;{{$platformInfo['qq2']}} @endif</p>
            <p>邮箱：@if(!empty($platformInfo['email'])) {{$platformInfo['email']}} @endif</p>
            <p>公司地址：@if(!empty($platformInfo['address'])) {{$platformInfo['address']}} @endif</p>
            @if(!empty($platformInfo['address2'])) <p>{{$platformInfo['address2']}} </p> @endif
            <p>微信：@if(!empty($platformInfo['wechat'])) {{$platformInfo['wechat']}} @endif</p>
            <script type="text/javascript" src="{{ $staticWapUrl }}js/api.js"></script>
            <div style=" width:100%; margin-top:2px;height:400px; margin-left:0px; " id="allmap">
                <script type="text/javascript">
                    // 百度地图API功能
                    var sContent = "@if(!empty($platformInfo['name'])) {{$platformInfo['name']}} @endif" +
                        "</div>";
                    var map = new BMap.Map("allmap");
                    map.addControl(new BMap.NavigationControl());  //添加默认缩放平移控件
                    map.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, type: BMAP_NAVIGATION_CONTROL_SMALL}));  //右上角，仅包含平移和缩放按钮
                    map.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_BOTTOM_LEFT, type: BMAP_NAVIGATION_CONTROL_PAN}));  //左下角，仅包含平移按钮
                    map.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_BOTTOM_RIGHT, type: BMAP_NAVIGATION_CONTROL_ZOOM}));  //右下角，仅包含缩放按钮
                    var point = new BMap.Point(@if(!empty($platformInfo['address_longitude'])) {{$platformInfo['address_longitude']}} @endif , @if(!empty($platformInfo['address_latitude'])) {{$platformInfo['address_latitude']}} @endif); //113.283672|22.834463
                    var marker = new BMap.Marker(point);
                    var infoWindow = new BMap.InfoWindow(sContent);  // 创建信息窗口对象
                    map.centerAndZoom(point, 15);
                    map.addOverlay(marker);
                    marker.openInfoWindow(infoWindow);
                    marker.addEventListener("click", function () {
                        this.openInfoWindow(infoWindow);
                        //图片加载完毕重绘infowindow
                        document.getElementById('imgDemo').onload = function () {
                            infoWindow.redraw();   //防止在网速较慢，图片未加载时，生成的信息框高度比图片的总高度小，导致图片部分被隐藏
                        }
                    });
                    /*

                    */</script>
            </div>
        </div>
    </div>

@endsection
