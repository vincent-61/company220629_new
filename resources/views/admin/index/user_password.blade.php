<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>修改密码</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/layui-v2.6.8/css/layui.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/font-awesome-4.7.0/css/font-awesome.min.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}css/public.css" media="all">
</head>
<body>

<style>
    .layui-form-item .layui-input-company {
        width: auto;
        padding-right: 10px;
        line-height: 38px;
    }
</style>
<div class="layuimini-container layuimini-page-anim">
    <div class="layuimini-main">

        <div class="layui-form layuimini-form">
            <div class="layui-form-item">
                <label class="layui-form-label required">旧的密码</label>
                <div class="layui-input-block">
                    <input type="password" name="old_password" lay-verify="required" lay-reqtext="旧的密码不能为空" placeholder="请输入旧的密码" value="" class="layui-input">
                    <tip>填写自己账号的旧的密码。</tip>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">新的密码</label>
                <div class="layui-input-block">
                    <input type="password" name="new_password" lay-verify="required" lay-reqtext="新的密码不能为空" placeholder="请输入新的密码" value="" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label required">再次输入密码</label>
                <div class="layui-input-block">
                    <input type="password" name="again_password" lay-verify="required" lay-reqtext="新的密码不能为空" placeholder="请输入新的密码" value="" class="layui-input">
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
        var  $ = layui.jquery,
            form = layui.form,
            layer = layui.layer;

        /**
         * 初始化表单，要加上，不然刷新部分组件可能会不加载
         */
        form.render();

        //监听提交
        form.on('submit(saveBtn)', function (data) {

            if (data.field.new_password !== '' && data.field.again_password !== '' ) {
                if (data.field.new_password !== data.field.again_password) {
                    layer.msg('两次输入的密码不一致，请重新输入', {
                        time: 1500
                    });
                    return false;
                } else {
                    // 加载层
                    var loading = layer.msg('处理中，请稍后...', {
                        icon: 16,
                        shade: 0.2
                    });
                    $.ajax({
                        url: '/admin/index/updatePassword',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'old_password': data.field.old_password,
                            'new_password': data.field.new_password,
                            'again_password': data.field.again_password,
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function(data){
                            layer.close(loading);
                            if (data.code === 0) {
                                layer.msg(data.message, {
                                    time: 1500
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
                }
            } else {
                layer.msg('新密码不可为空', {
                    time: 1500
                });
                return false;
            }

        });

    });
</script>

</body>
</html>
