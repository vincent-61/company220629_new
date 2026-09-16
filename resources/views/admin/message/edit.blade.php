<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>留言查看</title>
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
                <label class="layui-form-label">您的称呼</label>
                <div class="layui-input-block">
                    <input type="text" value="{{!empty($messageInfo['name']) ? $messageInfo['name'] : ''}}" class="layui-input" readonly="true" style="background:#eeeeee">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">联系电话</label>
                <div class="layui-input-block">
                    <input type="text" value="{{!empty($messageInfo['phone']) ? $messageInfo['phone'] : ''}}" class="layui-input" readonly="true" style="background:#eeeeee">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">邮件地址</label>
                <div class="layui-input-block">
                    <input type="text" value="{{!empty($messageInfo['email']) ? $messageInfo['email'] : ''}}" class="layui-input" readonly="true" style="background:#eeeeee">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">需求信息</label>
                <div class="layui-input-block">
                    <textarea name="content" id="content">{{!empty($messageInfo['content']) ? $messageInfo['content'] : ''}}</textarea>
                </div>
            </div>

            {{--<div class="layui-form-item">--}}
            {{--    <div class="layui-input-block">--}}
            {{--        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="saveBtn" id="saveBtn">确认保存</button>--}}
            {{--    </div>--}}
            {{--</div>--}}
        </div>
    </div>
</div>

<script src="{{ $staticAdminUrl }}lib/layui-v2.6.8/layui.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}js/lay-config.js?v=1.0.4" charset="utf-8"></script>
<script>
    layui.extend({
        layEditor: "layeditor/index",
        ace: "layeditor/ace/ace"
    }).use(['jquery', 'form', 'layEditor'], function () {
        var $ = layui.jquery,
            form = layui.form,
            layedit = layui.layEditor;

        /**
         * 初始化表单，要加上，不然刷新部分组件可能会不加载
         */
        form.render();

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
            height: '500px',
        });
        var ieditor = layedit.build('content');

    });
</script>

</body>
</html>
