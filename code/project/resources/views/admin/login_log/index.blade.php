<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>登录日志</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/layui-v2.6.8/css/layui.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/font-awesome-4.7.0/css/font-awesome.min.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}css/public.css" media="all">
</head>
<body>

<div class="layuimini-container layuimini-page-anim">
    <div class="layuimini-main">

        <fieldset class="table-search-fieldset">
            <legend>搜索信息</legend>
            <div style="margin: 10px 10px 10px 10px">
                <form class="layui-form layui-form-pane" action="">
                    <div class="layui-form-item">

                        <div class="layui-inline">
                            <label class="layui-form-label">昵称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="real_name" autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <button type="submit" class="layui-btn layui-btn-primary"  lay-submit lay-filter="data-search-btn"><i class="layui-icon"></i> 搜 索</button>
                        </div>
                    </div>
                </form>
            </div>
        </fieldset>

        <table class="layui-hide" id="currentTableId" lay-filter="currentTableFilter"></table>
    </div>
</div>

<script src="{{ $staticAdminUrl }}lib/jquery-3.4.1/jquery-3.4.1.min.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}lib/layui-v2.6.8/layui.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}lib/jq-module/jquery.particleground.min.js" charset="utf-8"></script>
<script>
    layui.use(['form', 'table','element'], function () {
        var $ = layui.jquery,
            form = layui.form,
            table = layui.table;

        /**
         * 初始化表单，要加上，不然刷新部分组件可能会不加载
         */
        form.render();

        table.render({
            elem: '#currentTableId',
            url: '/admin/loginLog/getLoginLogList?_token={{ csrf_token() }}',
            method: 'post',
            defaultToolbar: ['filter', 'exports', 'print'],
            cols: [[
                {field: 'username', width: 100, title: '用户名'},
                {field: 'real_name', width: 100, title: '昵称'},
                {field: 'operate', minwidth: 500, title: '操作内容'},
                {field: 'ip', width: 150, title: 'ip'},
                {field: 'created_at', width: 180, title: '创建时间'}
            ]],
            limits: [10, 15, 20, 25, 50, 100],
            limit: 15,
            page: true,
            // skin: 'line'
        });

        // 监听搜索操作
        form.on('submit(data-search-btn)', function (data) {
            //执行搜索重载
            table.reload('currentTableId', {
                page: {
                    curr: 1
                }
                , where: {
                    'real_name': data.field.real_name,
                }
            }, 'data');

            return false;
        });

    });

</script>

</body>
</html>

