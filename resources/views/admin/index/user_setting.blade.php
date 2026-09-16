<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>个人资料</title>
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
            <div class="layui-form-item">
                <label class="layui-form-label">账号名</label>
                <div class="layui-input-block">
                    <input type="text" value="{{$adminInfo['username']}}" class="layui-input" readonly="true" style="background:#eeeeee">
                    <!-- <tip>填写自己管理账号的名称。</tip> -->
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">角色名</label>
                <div class="layui-input-block">
                    <input type="text" value="{{$roleInfo['role_name']}}" class="layui-input" readonly="true" style="background:#eeeeee">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">昵称</label>
                <div class="layui-input-block">
                    <input type="real_name" name="real_name" lay-verify="required" lay-reqtext="昵称不能为空" placeholder="请输入昵称"  value="{{$adminInfo['real_name']}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">手机</label>
                <div class="layui-input-block">
                    <input type="number" name="phone" lay-verify="required" lay-reqtext="手机不能为空" placeholder="请输入手机"  value="{{$adminInfo['phone']}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">邮箱</label>
                <div class="layui-input-block">
                    <input type="email" name="email" placeholder="请输入邮箱"  value="{{$adminInfo['email']}}" class="layui-input">
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
    layui.use(['jquery', 'form'], function () {
        var $ = layui.jquery,
            form = layui.form,
            layer = layui.layer;

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
                url: '/admin/index/updateSetting',
                type: 'POST',
                dataType: 'json',
                data: {
                    'real_name': data.field.real_name,
                    'phone': data.field.phone,
                    'email': data.field.email,
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

    });
</script>

</body>
</html>
