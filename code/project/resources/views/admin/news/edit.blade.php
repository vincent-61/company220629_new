<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>新闻编辑</title>
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

            <input type="hidden" name="news_id" value="{{ !empty($newsInfo['news_id']) ? $newsInfo['news_id'] : '' }}">

            <div class="layui-form-item">
                <label class="layui-form-label required">新闻分类</label>
                <div class="layui-input-inline">
                    <input type="hidden" name="category_id" id="category_id" value="{{ !empty($newsInfo['category_id']) ? $newsInfo['category_id'] : '' }}">
                    <input type="text" name="category_name" id="category_name_select" placeholder="请选择分类" autocomplete="off" class="layui-input"  value="{{ !empty($newsInfo['category_name']) ? $newsInfo['category_name'] : ''}}" lay-verify="required" lay-reqtext="分类不能为空" readonly="true">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">标题</label>
                <div class="layui-input-block">
                    <input type="text" name="title" value="{{!empty($newsInfo['title']) ? $newsInfo['title'] : ''}}" class="layui-input" lay-verify="required" lay-reqtext="标题不能为空" placeholder="请输入标题">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">作者</label>
                <div class="layui-input-block">
                    <input type="text" name="author" value="{{!empty($newsInfo['author']) ? $newsInfo['author'] : 'admin'}}" class="layui-input" placeholder="请输入作者" lay-verify="required" lay-reqtext="作者不能为空">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">发布时间</label>
                <div class="layui-input-block">
                    <input type="text" name="news_time" id="news_time" value="{{!empty($newsInfo['news_time']) ? $newsInfo['news_time'] : date('Y-m-d H:i:s')}}" class="layui-input" placeholder="yyyy-MM-dd HH:mm:ss">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">排序</label>
                <div class="layui-input-block">
                    <input type="text" name="sort" value="{{!empty($newsInfo['sort']) ? $newsInfo['sort'] : 0}}" class="layui-input" placeholder="请输入排序（值越大，该产品在前台列表排序越靠前，值不可大于127）" lay-verify="number" oninput="if(value>127)value=127;if(value<0)value=0">
                    <span class="layui-badge-dot layui-bg-orange"></span><span class="layui-badge layui-bg-gray">排序值越大，该产品在前台列表排序越靠前，值不可大于127</span>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">新闻头图</label>
                <div class="layui-input-block">
                    <div class="layui-upload">
                        <button type="button" class="layui-btn" id="test1">上传图片</button>
                        <div class="layui-upload-list">
                            <img class="layui-upload-img" id="demo1" width="200px" src="{{!empty($newsInfo['img']) ? $newsInfo['img'] : ''}}">
                            <input type="hidden" name="img" id="img" value="{{!empty($newsInfo['img']) ? $newsInfo['img'] : ''}}">
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
                <label class="layui-form-label">新闻内容</label>
                <div class="layui-input-block">
                    <textarea name="content" id="content">{{!empty($newsInfo['content']) ? $newsInfo['content'] : ''}}</textarea>
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
    layui.extend({
        layEditor: "layeditor/index",
        ace: "layeditor/ace/ace"
    }).use(['jquery', 'form', 'layEditor', 'upload', 'element', 'layer', 'laydate', 'tableSelect'], function () {
        var $ = layui.jquery,
            form = layui.form,
            layer = layui.layer,
            layedit = layui.layEditor,
            upload = layui.upload,
            element = layui.element,
            laydate = layui.laydate,
            tableSelect = layui.tableSelect;

        /**
         * 初始化表单，要加上，不然刷新部分组件可能会不加载
         */
        form.render();

        //日期时间选择器
        laydate.render({
            elem: '#news_time'
            ,type: 'datetime'
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
            //暴露layupload参数设置接口 --详细查看layupload参数说明
            , uploadVideo: {
                url: '/admin/api/uploadEditorFile?_token={{ csrf_token() }}',
                field: 'file',//上传时的文件参数字段名
            }
            , uploadFiles: {
                url: '/admin/api/uploadEditorFile?_token={{ csrf_token() }}',
                field: 'file',//上传时的文件参数字段名
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
                'video',
                'attachment',
                '|',
                'preview',
            ],
            height: '500px',
        });
        var ieditor = layedit.build('content');

        //监听提交
        form.on('submit(saveBtn)', function (data) {
            // 加载层
            var loading = layer.msg('处理中，请稍后...', {
                icon: 16,
                shade: 0.2
            });

            $.ajax({
                url: '/admin/news/doEdit?_token={{ csrf_token() }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    'news_id': data.field.news_id,
                    'category_id': data.field.category_id,
                    'author': data.field.author,
                    'title': data.field.title,
                    'img': data.field.img,
                    'content': layedit.getContent(ieditor),
                    'sort': data.field.sort,
                    'news_time': data.field.news_time,
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

        tableSelect.render({
            elem: '#category_name_select',
            checkedKey: 'category_name_select',
            searchType: 'one',
            searchKey: 'category_name',
            searchPlaceholder: '请输入分类名称',
            table: {
                url: '/admin/newsCategory/getNewsCategoryList?_token={{ csrf_token() }}',
                where: {status: 1},
                method: 'post',
                cols: [[
                    { type: 'radio' },
                    { field: 'category_name', title: '分类名称'},
                ]]
            },
            done: function (elem, data) {
                var NEWJSON = []
                if (data.data.length === 0) {
                    $('#category_id').val('');
                } else {
                    layui.each(data.data, function (index, item) {
                        NEWJSON.push(item.category_name);
                        $('#category_id').val(item.category_id);
                    })
                }

                elem.val(NEWJSON.join(", "));
            }
        });

        //常规使用 - 普通图片上传
        var uploadInst = upload.render({
            elem: '#test1'
            ,url: '/admin/api/uploadNewsImg?_token={{ csrf_token() }}' //改成您自己的上传接口
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
