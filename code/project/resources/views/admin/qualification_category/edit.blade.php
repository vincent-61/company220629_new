<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>资质编辑</title>
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

            <input type="hidden" name="category_id" value="{{ !empty($categoryInfo['category_id']) ? $categoryInfo['category_id'] : '' }}">

            <div class="layui-form-item">
                <label class="layui-form-label required">分类名称</label>
                <div class="layui-input-block">
                    <input type="text" name="category_name" value="{{!empty($categoryInfo['category_name']) ? $categoryInfo['category_name'] : ''}}" class="layui-input" lay-verify="required" lay-reqtext="分类名称不能为空" placeholder="请输入分类名称">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">排序</label>
                <div class="layui-input-block">
                    <input type="text" name="sort" value="{{!empty($categoryInfo['sort']) ? $categoryInfo['sort'] : 0}}" class="layui-input" placeholder="请输入排序（值越大，该分类在前台列表排序越靠前，值不可大于127）" lay-verify="number" oninput="if(value>127)value=127;if(value<0)value=0">
                    <span class="layui-badge-dot layui-bg-orange"></span><span class="layui-badge layui-bg-gray">排序值越大，该分类在前台列表排序越靠前，值不可大于127</span>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn layui-btn-normal" lay-submit lay-filter="saveBtn" id="saveBtn">确认保存</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ $staticAdminUrl }}lib/layui-v2.6.8/layui.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}js/lay-config.js?v=1.0.4" charset="utf-8"></script>
<script>
    layui.use(['jquery', 'form', 'element', 'layer'], function () {
        var $ = layui.jquery,
            form = layui.form,
            layer = layui.layer,
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
                url: '/admin/qualificationCategory/doEdit?_token={{ csrf_token() }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    'category_id': data.field.category_id,
                    'category_name': data.field.category_name,
                    'sort': data.field.sort,
                },
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
    });
</script>

</body>
</html>
