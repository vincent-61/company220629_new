<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>基础信息</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/layui-v2.6.8/css/layui.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/font-awesome-4.7.0/css/font-awesome.min.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}css/public.css" media="all">
    <script src="/static_admin/js/lay-module/layeditor/ace/ace.js"></script>
</head>
<body>

<style>
    .layui-form-item .layui-input-company {width: auto;padding-right: 10px;line-height: 38px;}
</style>
<div class="layuimini-container layuimini-page-anim">
    <div class="layuimini-main">

        <div class="layui-form layuimini-form">
            <div class="layui-form-item">
                <label class="layui-form-label">公司名称</label>
                <div class="layui-input-block">
                    <input type="text" name="name" lay-verify="required" lay-reqtext="公司名称不能为空" placeholder="请输入公司名称"  value="{{!empty($info['name']) ? $info['name'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">公司Logo</label>
                <div class="layui-input-block">
                    <div class="layui-upload">
                        <button type="button" class="layui-btn" id="test1">上传Logo</button>
                        <div class="layui-upload-list">
                            <img class="layui-upload-img" id="demo1" width="200px" src="{{!empty($info['logo']) ? $info['logo'] : ''}}">
                            <input type="hidden" name="logo" id="logo" value="{{!empty($info['logo']) ? $info['logo'] : ''}}">
                            <p id="demo1Text"></p>
                        </div>
                        <div style="width: 95px;">
                            <div class="layui-progress layui-progress-big" lay-showpercent="yes" lay-filter="demo1">
                                <div class="layui-progress-bar" lay-percent=""></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">SEO标题</label>
                <div class="layui-input-block">
                    <input type="text" name="title" lay-verify="required" lay-reqtext="SEO标题不能为空" placeholder="请输入SEO标题"  value="{{!empty($info['title']) ? $info['title'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">SEO关键字</label>
                <div class="layui-input-block">
                    <input type="text" name="keywords" lay-verify="required" lay-reqtext="SEO关键字不能为空" placeholder="请输入SEO关键字"  value="{{!empty($info['keywords']) ? $info['keywords'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">SEO描述</label>
                <div class="layui-input-block">
                    <input type="text" name="description" lay-verify="required" lay-reqtext="SEO描述不能为空" placeholder="请输入SEO描述"  value="{{!empty($info['description']) ? $info['description'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">主营产品</label>
                <div class="layui-input-block">
                    <input type="text" name="main_product" lay-verify="required" lay-reqtext="主营产品不能为空" placeholder="请输入主营产品"  value="{{!empty($info['main_product']) ? $info['main_product'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">首页_关于我们</label>
                <div class="layui-input-block">
                    <textarea name="about" id="about">{{$info['about']}}</textarea>
                </div>
            </div>

            {{--<div class="layui-form-item">--}}
            {{--    <label class="layui-form-label required">首页_关于我们图片</label>--}}
            {{--    <div class="layui-input-block">--}}
            {{--        <div class="layui-upload">--}}
            {{--            <button type="button" class="layui-btn" id="test2">上传Logo</button>--}}
            {{--            <div class="layui-upload-list">--}}
            {{--                <img class="layui-upload-img" id="demo2" width="200px" src="{{!empty($info['about_img']) ? $info['about_img'] : ''}}">--}}
            {{--                <input type="hidden" name="about_img" id="about_img" value="{{!empty($info['about_img']) ? $info['about_img'] : ''}}">--}}
            {{--                <p id="demoText2"></p>--}}
            {{--            </div>--}}
            {{--            <div style="width: 95px;">--}}
            {{--                <div class="layui-progress layui-progress-big" lay-showpercent="yes" lay-filter="demo2">--}}
            {{--                    <div class="layui-progress-bar" lay-percent=""></div>--}}
            {{--                </div>--}}
            {{--            </div>--}}
            {{--        </div>--}}
            {{--    </div>--}}
            {{--</div>--}}

            {{--<div class="layui-form-item">--}}
            {{--    <label class="layui-form-label required">首页_关于我们视频</label>--}}
            {{--    <div class="layui-input-block">--}}
            {{--        <button type="button" class="layui-btn" id="about_video"><i class="layui-icon"></i>上传视频</button>--}}
            {{--        <button type="button" class="layui-btn" id="about_img"><i class="layui-icon"></i>上传视频封面</button>--}}
            {{--        <br />--}}
            {{--        <input type="hidden" name="about_img" value="{{ !empty($info['about_img']) ? $info['about_img'] : '' }}">--}}
            {{--        <input type="hidden" name="about_video" value="{{ !empty($info['about_video']) ? $info['about_video'] : '' }}">--}}
            {{--        <video width="50%" id="aboutVideoShow" src="{{ !empty($info['about_video']) ? $info['about_video'] : '' }}" poster="{{ !empty($info['about_img']) ? $info['about_img'] : '' }}" undefined="" controls="controls">您的浏览器不支持video播放</video>--}}
            {{--    </div>--}}
            {{--</div>--}}

            <div class="layui-form-item">
                <label class="layui-form-label required">首页_关于我们</label>
                <div class="layui-input-block">
                    <div class="layui-upload">
                        <button type="button" class="layui-btn" id="test2">上传图片</button>
                        <div class="layui-upload-list">
                            <img class="layui-upload-img" id="demo2" width="200px" src="{{!empty($info['about_img']) ? $info['about_img'] : ''}}">
                            <input type="hidden" name="about_img" id="about_img" value="{{!empty($info['about_img']) ? $info['about_img'] : ''}}">
                            <p id="demo2Text"></p>
                        </div>
                        <div style="width: 95px;">
                            <div class="layui-progress layui-progress-big" lay-showpercent="yes" lay-filter="demo1">
                                <div class="layui-progress-bar" lay-percent=""></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">销售经理</label>
                <div class="layui-input-block">
                    <input type="text" name="contact_name" lay-verify="required" lay-reqtext="销售经理不能为空" placeholder="请输入销售经理" value="{{!empty($info['contact_name']) ? $info['contact_name'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">联系座机</label>
                <div class="layui-input-block">
                    <input type="text" name="telephone" lay-verify="required" lay-reqtext="联系座机不能为空" placeholder="请输入联系座机" value="{{!empty($info['telephone']) ? $info['telephone'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">联系手机</label>
                <div class="layui-input-block">
                    <input type="text" name="phone" lay-verify="required" lay-reqtext="联系手机不能为空" placeholder="请输入联系手机" value="{{!empty($info['phone']) ? $info['phone'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">联系手机2</label>
                <div class="layui-input-block">
                    <input type="text" name="phone2" placeholder="请输入联系手机2" value="{{!empty($info['phone2']) ? $info['phone2'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">QQ</label>
                <div class="layui-input-block">
                    <input type="text" name="qq" lay-verify="required" lay-reqtext="QQ不能为空" placeholder="请输入QQ" value="{{!empty($info['qq']) ? $info['qq'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">QQ2</label>
                <div class="layui-input-block">
                    <input type="text" name="qq2" placeholder="请输入QQ2" value="{{!empty($info['qq2']) ? $info['qq2'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">邮箱</label>
                <div class="layui-input-block">
                    <input type="text" name="email" lay-verify="required" lay-reqtext="邮箱不能为空" placeholder="请输入邮箱" value="{{!empty($info['email']) ? $info['email'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">公司地址</label>
                <div class="layui-input-block">
                    <input type="text" name="address" lay-verify="required" lay-reqtext="公司地址不能为空" placeholder="请输入公司地址" value="{{!empty($info['address']) ? $info['address'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">公司地址2</label>
                <div class="layui-input-block">
                    <input type="text" name="address2" placeholder="请输入公司地址2" value="{{!empty($info['address2']) ? $info['address2'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">公司地址经度</label>
                <div class="layui-input-block">
                    <input type="text" name="address_longitude" lay-verify="required" lay-reqtext="公司地址经度不能为空" placeholder="请输入公司地址经度" value="{{!empty($info['address_longitude']) ? $info['address_longitude'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">公司地址维度</label>
                <div class="layui-input-block">
                    <input type="text" name="address_latitude" lay-verify="required" lay-reqtext="公司地址维度不能为空" placeholder="请输入公司地址维度" value="{{!empty($info['address_latitude']) ? $info['address_latitude'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">公司地址2经度</label>
                <div class="layui-input-block">
                    <input type="text" name="address2_longitude" placeholder="请输入公司地址2经度" value="{{!empty($info['address2_longitude']) ? $info['address2_longitude'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">公司地址2维度</label>
                <div class="layui-input-block">
                    <input type="text" name="address2_latitude" placeholder="请输入公司地址2维度" value="{{!empty($info['address2_latitude']) ? $info['address2_latitude'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">微信</label>
                <div class="layui-input-block">
                    <input type="text" name="wechat" lay-verify="required" lay-reqtext="微信不能为空" placeholder="请输入微信" value="{{!empty($info['wechat']) ? $info['wechat'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">微信二维码</label>
                <div class="layui-input-block">
                    <div class="layui-upload">
                        <button type="button" class="layui-btn" id="test3">上传Logo</button>
                        <div class="layui-upload-list">
                            <img class="layui-upload-img" id="demo3" width="200px" src="{{!empty($info['wechat_img']) ? $info['wechat_img'] : ''}}">
                            <input type="hidden" name="wechat_img" id="wechat_img" value="{{!empty($info['wechat_img']) ? $info['wechat_img'] : ''}}">
                            <p id="demo3Text"></p>
                        </div>
                        <div style="width: 95px;">
                            <div class="layui-progress layui-progress-big" lay-showpercent="yes" lay-filter="demo3">
                                <div class="layui-progress-bar" lay-percent=""></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">版权</label>
                <div class="layui-input-block">
                    <input type="text" name="copyright" lay-verify="required" lay-reqtext="版权不能为空" placeholder="请输入版权" value="{{!empty($info['copyright']) ? $info['copyright'] : ''}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">公司简介</label>
                <div class="layui-input-block">
                    <textarea name="introduce" id="introduce">{{$info['introduce']}}</textarea>
                </div>
            </div>

            {{--<div class="layui-form-item">--}}
            {{--    <label class="layui-form-label">厂家视频</label>--}}
            {{--    <div class="layui-input-block">--}}
            {{--        <button type="button" class="layui-btn" id="video"><i class="layui-icon"></i>上传视频</button>--}}
            {{--        <button type="button" class="layui-btn" id="video_poster"><i class="layui-icon"></i>上传视频封面</button>--}}
            {{--        <br />--}}
            {{--        <input type="hidden" name="video" value="{{ !empty($info['video']) ? $info['video'] : '' }}">--}}
            {{--        <input type="hidden" name="video_poster" value="{{ !empty($info['video_poster']) ? $info['video_poster'] : '' }}">--}}
            {{--        <video width="50%" id="videoShow" src="{{ !empty($info['video']) ? $info['video'] : '' }}" poster="{{ !empty($info['video_poster']) ? $info['video_poster'] : '' }}" undefined="" controls="controls">您的浏览器不支持video播放</video>--}}

            {{--    </div>--}}
            {{--</div>--}}

            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn layui-btn-normal" lay-submit lay-filter="saveBtn">确认保存</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ $staticAdminUrl }}lib/layui-v2.6.8/layui.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}js/lay-config.js?v=1.0.4" charset="utf-8"></script>
<script>

    layui.extend({
        layEditor: "layeditor/index",
        ace: "layeditor/ace/ace"
    }).use(['jquery', 'form', 'upload', 'element', 'layEditor'], function () {
        var $ = layui.jquery,
            form = layui.form,
            layer = layui.layer,
            upload = layui.upload,
            element = layui.element,
            layedit = layui.layEditor;

        /**
         * 初始化表单，要加上，不然刷新部分组件可能会不加载
         */
        form.render();

        upload.render({
            elem: '#video'
            ,url: '/admin/api/uploadEditorFile?_token={{ csrf_token() }}'
            ,accept: 'video' //视频
            ,done: function(res){
                layer.msg('上传成功');
                if (res.code == 0) {
                    $("#videoShow").attr('src', res.data.src)
                    $("input[name='video']").val(res.data.src)
                }
            }
        });

        upload.render({
            elem: '#video_poster'
            ,url: '/admin/api/uploadEditorFile?_token={{ csrf_token() }}'
            ,accept: 'image' //图片
            ,done: function(res){
                layer.msg('上传成功');
                if (res.code == 0) {
                    $("#videoShow").attr('poster', res.data.src)
                    $("input[name='video_poster']").val(res.data.src)
                }
            }
        });

        upload.render({
            elem: '#about_video'
            ,url: '/admin/api/uploadEditorFile?_token={{ csrf_token() }}'
            ,accept: 'video' //视频
            ,done: function(res){
                layer.msg('上传成功');
                if (res.code == 0) {
                    $("#aboutVideoShow").attr('src', res.data.src)
                    $("input[name='about_video']").val(res.data.src)
                }
            }
        });

        upload.render({
            elem: '#about_img'
            ,url: '/admin/api/uploadEditorFile?_token={{ csrf_token() }}'
            ,accept: 'image' //图片
            ,done: function(res){
                layer.msg('上传成功');
                if (res.code == 0) {
                    $("#aboutVideoShow").attr('poster', res.data.src)
                    $("input[name='about_img']").val(res.data.src)
                }
            }
        });

        layedit.set({
            //暴露layupload参数设置接口 --详细查看layupload参数说明
            uploadImage: {
                url: '/admin/api/uploadEditorImg?_token={{ csrf_token() }}',
                field: 'file',//上传时的文件参数字段名
                accept: 'image',
                acceptMime: 'image/*',
                exts: 'jpg|png|gif|bmp|jpeg',
                size: 1024 * 10,
                done: function (data) {//文件上传接口返回code为0时的回调
                }
            }
            //开发者模式 --默认为false
            , devmode: true
            , tool: [
                'html',
                'undo',
                'redo',
                'code',
                'strong',
                'italic',
                'underline',
                'del',
                'addhr',
                '|',
                'removeformat',
                'fontFomatt',
                'fontfamily',
                'fontSize',
                'lineHeight',
                'fontBackColor',
                '|',
                'left',
                'center',
                'right',
                '|',
                'link',
                'unlink',
                'image_alt',
                '|',
                'preview',
            ],
            height: '200px',
        });
        var ieditor1 = layedit.build('about');

        layedit.set({
            //暴露layupload参数设置接口 --详细查看layupload参数说明
            uploadImage: {
                url: '/admin/api/uploadEditorImg?_token={{ csrf_token() }}',
                field: 'file',//上传时的文件参数字段名
                accept: 'image',
                acceptMime: 'image/*',
                exts: 'jpg|png|gif|bmp|jpeg',
                size: 1024 * 10,
                done: function (data) {//文件上传接口返回code为0时的回调
                }
            }
            //开发者模式 --默认为false
            , devmode: true
            , tool: [
                'html',
                'undo',
                'redo',
                'code',
                'strong',
                'italic',
                'underline',
                'del',
                'addhr',
                '|',
                'removeformat',
                'fontFomatt',
                'fontfamily',
                'fontSize',
                'lineHeight',
                'fontBackColor',
                '|',
                'left',
                'center',
                'right',
                '|',
                'link',
                'unlink',
                'image_alt',
                '|',
                'preview',
            ],
            height: '400px',
        });
        var ieditor2 = layedit.build('introduce');

        //监听提交
        form.on('submit(saveBtn)', function (data) {
            // 加载层
            var loading = layer.msg('处理中，请稍后...', {
                icon: 16,
                shade: 0.2
            });
            $.ajax({
                url: '/admin/platform/doEdit',
                type: 'POST',
                dataType: 'json',
                data: {
                    'name': data.field.name,
                    'logo': data.field.logo,
                    'title': data.field.title,
                    'keywords': data.field.keywords,
                    'description': data.field.description,
                    'main_product': data.field.main_product,
                    'about': layedit.getContent(ieditor1),
                    'about_img': data.field.about_img,
                    // 'about_video': data.field.about_video,
                    'contact_name': data.field.contact_name,
                    'telephone': data.field.telephone,
                    'phone': data.field.phone,
                    'phone2': data.field.phone2,
                    'qq': data.field.qq,
                    'qq2': data.field.qq2,
                    'email': data.field.email,
                    'address': data.field.address,
                    'address2': data.field.address2,
                    'address_longitude': data.field.address_longitude,
                    'address_latitude': data.field.address_latitude,
                    'address2_longitude': data.field.address2_longitude,
                    'address2_latitude': data.field.address2_latitude,
                    'wechat': data.field.wechat,
                    'wechat_img': data.field.wechat_img,
                    'copyright': data.field.copyright,
                    'introduce': layedit.getContent(ieditor2),
                    // 'video': data.field.video,
                    // 'video_poster': data.field.video_poster,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(data){
                    layer.close(loading);
                    if (data.code === 0) {
                        layer.msg(data.message, {
                            time: 1000
                        },function(index){
                            location.reload();
                        });
                    } else if (data.code === 1001) {
                        layer.alert(data.message, {
                            icon: 0,
                            skin: 'layer-ext-moon'
                        }, function(index) {
                            location.reload();
                        });
                    } else if (data.code === 1002) {
                        layer.alert(data.message, {
                            icon: 2,
                            skin: 'layer-ext-moon'
                        },function(index){
                            location.reload();
                        });
                    }
                }
            });
            return false;
        });

        // 公司logo上传
        var uploadInst = upload.render({
            elem: '#test1'
            ,url: '/admin/api/uploadLogo?_token={{ csrf_token() }}' //改成您自己的上传接口
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo1').attr('src', result); //图片链接（base64）
                });

                element.progress('demo1', '0%'); //进度条复位
                layer.msg('上传中', {icon: 16, time: 0});
            }
            ,done: function(res){
                if (res.code === 0) {
                    $('#logo').attr('value', res.data.url)
                } else {
                    return layer.msg('上传失败');
                }
                $('#demo1Text').html(''); //置空上传失败的状态
            }
            //进度条
            ,progress: function(n, elem, e){
                element.progress('demo1', n + '%'); //可配合 layui 进度条元素使用
                if(n == 100){
                    layer.msg('上传完毕', {icon: 1});
                }
            }
        });

        // 首页_关于我们图片上传
        var uploadInst = upload.render({
            elem: '#test2'
            ,url: '/admin/api/uploadLogo?_token={{ csrf_token() }}' //改成您自己的上传接口
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo2').attr('src', result); //图片链接（base64）
                });

                element.progress('demo2', '0%'); //进度条复位
                layer.msg('上传中', {icon: 16, time: 0});
            }
            ,done: function(res){
                if (res.code === 0) {
                    $('#about_img').attr('value', res.data.url)
                } else {
                    return layer.msg('上传失败');
                }
                $('#demo2Text').html(''); //置空上传失败的状态
            }
            //进度条
            ,progress: function(n, elem, e){
                element.progress('demo2', n + '%'); //可配合 layui 进度条元素使用
                if(n == 100){
                    layer.msg('上传完毕', {icon: 1});
                }
            }
        });

        // 联系微信二维码上传
        var uploadInst = upload.render({
            elem: '#test3'
            ,url: '/admin/api/uploadLogo?_token={{ csrf_token() }}' //改成您自己的上传接口
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo3').attr('src', result); //图片链接（base64）
                });

                element.progress('demo3', '0%'); //进度条复位
                layer.msg('上传中', {icon: 16, time: 0});
            }
            ,done: function(res){
                if (res.code === 0) {
                    $('#wechat_img').attr('value', res.data.url)
                } else {
                    return layer.msg('上传失败');
                }
                $('#demo3Text').html(''); //置空上传失败的状态
            }
            //进度条
            ,progress: function(n, elem, e){
                element.progress('demo3', n + '%'); //可配合 layui 进度条元素使用
                if(n == 100){
                    layer.msg('上传完毕', {icon: 1});
                }
            }
        });

        // 联系微信二维码上传
        var uploadInst = upload.render({
            elem: '#test4'
            ,url: '/admin/api/uploadLogo?_token={{ csrf_token() }}' //改成您自己的上传接口
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo4').attr('src', result); //图片链接（base64）
                });

                element.progress('demo4', '0%'); //进度条复位
                layer.msg('上传中', {icon: 16, time: 0});
            }
            ,done: function(res){
                if (res.code === 0) {
                    $('#address_img').attr('value', res.data.url)
                } else {
                    return layer.msg('上传失败');
                }
                $('#demo4Text').html(''); //置空上传失败的状态
            }
            //进度条
            ,progress: function(n, elem, e){
                element.progress('demo4', n + '%'); //可配合 layui 进度条元素使用
                if(n == 100){
                    layer.msg('上传完毕', {icon: 1});
                }
            }
        });

    });
</script>

</body>
</html>
