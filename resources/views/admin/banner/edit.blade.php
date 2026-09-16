<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>轮播图编辑</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/layui-v2.6.8/css/layui.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/font-awesome-4.7.0/css/font-awesome.min.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}css/public.css" media="all">
</head>
<body>

<style>
    .layui-form-item .layui-input-company {width: auto;padding-right: 10px;line-height: 38px;}
</style>
<div class="layuimini-container layuimini-page-anim">
    <div class="layuimini-main">

        <div class="layui-form layuimini-form">

            <input type="hidden" name="banner_id" value="{{ !empty($bannerInfo['banner_id']) ? $bannerInfo['banner_id'] : '' }}">

            <div class="layui-form-item">
                <label class="layui-form-label">备注</label>
                <div class="layui-input-block">
                    <input type="text" name="remark" value="{{!empty($bannerInfo['remark']) ? $bannerInfo['remark'] : ''}}" class="layui-input" placeholder="请输入备注">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">排序</label>
                <div class="layui-input-block">
                    <input type="text" name="sort" value="{{!empty($bannerInfo['sort']) ? $bannerInfo['sort'] : 0}}" class="layui-input" placeholder="请输入排序（值越大，该产品在前台列表排序越靠前，值不可大于127）" lay-verify="number" oninput="if(value>127)value=127;if(value<0)value=0">
                    <span class="layui-badge-dot layui-bg-orange"></span><span class="layui-badge layui-bg-gray">排序值越大，该产品在前台列表排序越靠前，值不可大于127</span>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">跳转链接</label>
                <div class="layui-input-block">
                    <input type="text" name="url" value="{{!empty($bannerInfo['url']) ? $bannerInfo['url'] : ''}}" class="layui-input" placeholder="请输入跳转链接（如：https://www.baidu.com）">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">图片</label>
                <div class="layui-input-block">
                    <div class="layui-upload">
                        <button type="button" class="layui-btn" id="test1">上传图片</button>
                        <div class="layui-upload-list">
                            <img class="layui-upload-img" id="demo1" width="200px" src="{{!empty($bannerInfo['img']) ? $bannerInfo['img'] : ''}}">
                            <input type="hidden" name="img" id="img" value="{{!empty($bannerInfo['img']) ? $bannerInfo['img'] : ''}}">
                            <p id="demoText"></p>
                        </div>
                        <div style="width: 95px;">
                            <div class="layui-progress layui-progress-big" lay-showpercent="yes" lay-filter="demo">
                                <div class="layui-progress-bar" lay-percent=""></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
    layui.use(['jquery', 'form', 'upload', 'element',], function () {
        var $ = layui.jquery,
            form = layui.form,
            layer = layui.layer,
            upload = layui.upload,
            element = layui.element;

        /**
         * 初始化表单，要加上，不然刷新部分组件可能会不加载
         */
        form.render();

        //监听提交
        form.on('submit(saveBtn)', function (data) {
            // 加载层
            var loading = layer.msg('处理中，请稍后...', {
                icon: 16,
                shade: 0.2
            });
            $.ajax({
                url: '/admin/banner/doEdit?_token={{ csrf_token() }}',
                type: 'POST',
                dataType: 'json',
                data: data.field,
                success: function(data){
                    layer.close(loading);
                    if (data.code === 0) {
                        layer.msg(data.message, {
                            time: 1000
                        },function(index){
                            var iframeIndex = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(iframeIndex);
                            parent.location.reload();
                        });
                    } else {
                        layer.alert(data.message, {
                            icon: 0,
                            skin: 'layer-ext-moon'
                        }, function(index) {
                            layer.close(index);
                        });
                    }
                }
            });
            return false;
        });

        //常规使用 - 普通图片上传
        var uploadInst = upload.render({
            elem: '#test1'
            ,url: '/admin/api/uploadBannerImg?_token={{ csrf_token() }}' //改成您自己的上传接口
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo1').attr('src', result); //图片链接（base64）
                });

                element.progress('demo', '0%'); //进度条复位
                layer.msg('上传中', {icon: 16, time: 0});
            }
            ,done: function(res){
                if (res.code === 0) {
                    $('#img').attr('value', res.data.url)
                } else {
                    return layer.msg('上传失败');
                }
                $('#demoText').html(''); //置空上传失败的状态
            }
            //进度条
            ,progress: function(n, elem, e){
                element.progress('demo', n + '%'); //可配合 layui 进度条元素使用
                if(n == 100){
                    layer.msg('上传完毕', {icon: 1});
                }
            }
        });

    });
</script>

</body>
</html>
