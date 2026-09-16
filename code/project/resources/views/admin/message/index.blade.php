<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>留言管理</title>
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
                            <label class="layui-form-label">姓名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="name" autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">联系方式</label>
                            <div class="layui-input-inline">
                                <input type="text" name="phone" autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <button type="submit" class="layui-btn layui-btn-primary"  lay-submit lay-filter="data-search-btn"><i class="layui-icon"></i> 搜 索</button>
                        </div>

                    </div>
                </form>
            </div>
        </fieldset>

        <script type="text/html" id="toolbarDemo">
            <div class="layui-btn-container">
                {{--<button class="layui-btn layui-btn-normal layui-btn-sm data-add-btn" lay-event="add"> 添加 </button>--}}
                <button class="layui-btn layui-btn-danger layui-btn-sm data-del-btn" lay-event="delBatch"> 批量删除 </button>
            </div>
        </script>

        <table class="layui-hide" id="currentTableId" lay-filter="currentTableFilter"></table>

        <script type="text/html" id="currentTableBar">
            <a class="layui-btn layui-btn-normal layui-btn-xs data-count-edit" lay-event="edit">查看</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs data-count-del" lay-event="delete">删除</a>
        </script>

    </div>
</div>

<script src="{{ $staticAdminUrl }}lib/layui-v2.6.8/layui.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}js/lay-config.js?v=1.0.4" charset="utf-8"></script>
<script>
    layui.use(['form', 'table', 'tableSelect'], function () {
        var $ = layui.jquery,
            form = layui.form,
            table = layui.table,
            tableSelect = layui.tableSelect;

        /**
         * 初始化表单，要加上，不然刷新部分组件可能会不加载
         */
        form.render();

        table.render({
            elem: '#currentTableId',
            url: '/admin/message/getMessageList?_token={{ csrf_token() }}',
            method: 'post',
            toolbar: '#toolbarDemo',
            defaultToolbar: ['filter'],
            cols: [[
                {type: "checkbox", width: 50},
                {field: 'name', minWidth: 120, title: '您的称呼'},
                {field: 'phone', minWidth: 120, title: '联系电话'},
                {field: 'email', minWidth: 180, title: '邮件地址'},
                {field: 'content', minWidth: 200, align: "center", title: '需求信息'},
                {field: 'ip', minWidth: 200, align: "center", title: 'ip信息'},
                {field: 'created_at', minWidth: 170, title: '留言时间'},
                {title: '操作', width: 120, toolbar: '#currentTableBar', align: "center", fixed: 'right'}
            ]],
            limits: [10, 15, 20, 25, 50, 100],
            limit: 15,
            page: true,
        });

        // 监听搜索操作
        form.on('submit(data-search-btn)', function (data) {
            //执行搜索重载
            table.reload('currentTableId', {
                page: {
                    curr: 1
                }
                , where: {
                    'name': data.field.name,
                    'phone': data.field.phone,
                }
            }, 'data');

            return false;
        });

        /**
         * toolbar事件监听
         */
        table.on('toolbar(currentTableFilter)', function (obj) {
            if (obj.event === 'delBatch') {
                var checkStatus = table.checkStatus('currentTableId')
                    , data = checkStatus.data;
                var newData = data.map(function(value,index) {
                    return value.message_id;
                })

                if (checkStatus.data.length >= 1) {
                    layer.confirm('确认批量删除所选留言？删除后无法恢复',{
                        icon: 3,
                        skin: 'layer-ext-moon'
                    },function(){
                        $.ajax({
                            url: "/admin/message/doDelBatch?_token={{ csrf_token() }}",
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'data': JSON.stringify(newData),
                            },
                            success: function(result){
                                if(result.code === 0){
                                    layer.msg(result.message, {
                                        time: 1000
                                    },function(index){
                                        location.reload();
                                    });
                                }else{
                                    layer.msg(result.message, {time: 2000});
                                }
                            }
                        });
                    });

                    return false;
                } else {
                    layer.msg('请选择数据进行批量删除', {icon: 7});
                    return false;
                }
            }
        });

        table.on('tool(currentTableFilter)', function (obj) {
            var data = obj.data;
            if (obj.event === 'edit') {
                var index = layer.open({
                    title: '查看留言',
                    type: 2,
                    shade: 0.2,
                    maxmin:true,
                    shadeClose: true,
                    area: ['100%', '100%'],
                    content: '/admin/message/edit?message_id=' + data.message_id,
                });
                $(window).on("resize", function () {
                    layer.full(index);
                });
                return false;

            } else if (obj.event === 'delete') {
                layer.confirm('确认删除？删除后不能恢复。', function (index) {
                    $.ajax({
                        url: "/admin/message/setMessageStatus?_token={{ csrf_token() }}",
                        type: "post",
                        dataType: "json",
                        data: {
                            "message_id": data.message_id,
                            "status": 2
                        },
                        success: function(result){
                            if(result.code === 0){
                                obj.del();
                                layer.close(index);
                            }else{
                                layer.msg(result.message, {time: 2000});
                            }
                        }
                    });
                    return false;
                });
            }
        });

    });

</script>

</body>
</html>
