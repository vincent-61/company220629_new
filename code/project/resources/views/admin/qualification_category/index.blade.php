<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>资质分类</title>
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
                            <label class="layui-form-label">分类</label>
                            <div class="layui-input-inline">
                                <input type="text" name="category_name" autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-inline">
                                <select name="status" lay-filter="aihao">
                                    <option value="">请选择</option>
                                    <option value="1">启动</option>
                                    <option value="2">停用</option>
                                </select>
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
                <button class="layui-btn layui-btn-normal layui-btn-sm data-add-btn" lay-event="add"> 添加 </button>
            </div>
        </script>

        <table class="layui-hide" id="currentTableId" lay-filter="currentTableFilter"></table>

        <script type="text/html" id="currentTableBar">
            <a class="layui-btn layui-btn-normal layui-btn-xs data-count-edit" lay-event="edit">编辑</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs data-count-del" lay-event="delete">删除</a>
        </script>

    </div>
</div>

<script src="{{ $staticAdminUrl }}lib/layui-v2.6.8/layui.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}js/lay-config.js?v=1.0.4" charset="utf-8"></script>
<script>
    layui.use(['form', 'table'], function () {
        var $ = layui.jquery,
            form = layui.form,
            table = layui.table;

        /**
         * 初始化表单，要加上，不然刷新部分组件可能会不加载
         */
        form.render();

        table.render({
            elem: '#currentTableId',
            url: '/admin/qualificationCategory/getQualificationCategoryList?_token={{ csrf_token() }}',
            method: 'post',
            toolbar: '#toolbarDemo',
            defaultToolbar: ['filter'],
            cols: [[
                // {field: 'category_id', width: 80, title: '分类ID'},
                {field: 'category_name', minWidth: 120, title: '分类名称'},
                {field: 'sort', width: 80, title: '排序'},
                {
                    field: 'status', minWidth: 80, align: "center", templet: function (d) {
                        if (d.status === 1) {
                            return '<i class="layui-icon layui-icon-ok" style="color: #393;" lay-event="setQualificationCategoryStatus"></i>';
                        } else if (d.status === 2) {
                            return '<i class="layui-icon layui-icon-close" style="color: #f00;" lay-event="setQualificationCategoryStatus"></i>'
                        }
                    }, title: '状态'
                },
                {field: 'created_at', minWidth: 170, title: '创建时间'},
                {title: '操作', minWidth: 120, toolbar: '#currentTableBar', align: "center", fixed: 'right'}
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
                    'category_name': data.field.category_name,
                    'status': data.field.status,
                }
            }, 'data');

            return false;
        });

        /**
         * toolbar事件监听
         */
        table.on('toolbar(currentTableFilter)', function (obj) {
            if (obj.event === 'add') {   // 监听添加操作
                var index = layer.open({
                    title: '添加分类',
                    type: 2,
                    shade: 0.2,
                    maxmin:true,
                    shadeClose: true,
                    area: ['100%', '100%'],
                    content: '/admin/qualificationCategory/edit',
                });
                $(window).on("resize", function () {
                    layer.full(index);
                });
            }
        });

        table.on('tool(currentTableFilter)', function (obj) {
            var data = obj.data;
            if (obj.event === 'edit') {
                var index = layer.open({
                    title: '编辑分类',
                    type: 2,
                    shade: 0.2,
                    maxmin:true,
                    shadeClose: true,
                    area: ['100%', '100%'],
                    content: '/admin/qualificationCategory/edit?category_id=' + data.category_id,
                });
                $(window).on("resize", function () {
                    layer.full(index);
                });
                return false;

            } else if (obj.event === 'setQualificationCategoryStatus') {

                var status = data.status === 1 ? 2 : 1;
                $.ajax({
                    url: "/admin/qualificationCategory/setQualificationCategoryStatus?_token={{ csrf_token() }}",
                    type: "post",
                    dataType: "json",
                    data: {
                        "category_id": data.category_id,
                        "status": status
                    },
                    success: function(result){
                        if(result.code === 0){
                            obj.update({
                                status: status
                            });
                        }else{
                            layer.msg(result.message, {time: 2000});
                        }
                    }
                });
                return false;
            } else if (obj.event === 'delete') {
                layer.confirm('确认删除？删除后不能恢复。', function (index) {
                    $.ajax({
                        url: "/admin/qualificationCategory/setQualificationCategoryStatus?_token={{ csrf_token() }}",
                        type: "post",
                        dataType: "json",
                        data: {
                            "category_id": data.category_id,
                            "status": 3
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
