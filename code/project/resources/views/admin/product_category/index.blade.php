<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>分类</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/layui-v2.6.8/css/layui.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/font-awesome-4.7.0/css/font-awesome.min.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}css/public.css" media="all">
</head>

<style type="text/css">
    .layui-table-cell {
        height: auto;
    }
</style>

<body>

<div class="layuimini-container layuimini-page-anim">
    <div class="layuimini-main">

        <div>
            <button class="layui-btn layui-btn-normal" id="addTopCategory">添加分类</button>
            <table id="category-table" class="layui-table" lay-filter="category-table"></table>
        </div>

    </div>
</div>

<script src="{{ $staticAdminUrl }}lib/layui-v2.6.8/layui.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}js/lay-config.js?v=1.0.4" charset="utf-8"></script>
<script>
    layui.use(['table', 'treetable'], function () {
        var $ = layui.jquery,
            table = layui.table,
            treetable = layui.treetable;

        $.ajax({
            url: '/admin/productCategory/getProductCategoryList?_token={{ csrf_token() }}',
            type: 'post',
            dataType: 'json',
            success: function(res) {
                renderTable(res.data);
            }
        })

        var renderTable = function(data) {
            // 渲染表格
            layer.load(2);
            treetable.render({
                treeColIndex: 1,
                treeSpid: 0,
                treeIdName: 'category_id',
                treePidName: 'parent_id',
                elem: '#category-table',
                data: data,
                page: false,
                cols: [[
                    {type: 'numbers'},
                    {field: 'category_name', minWidth: 200, title: '分类名称'},
                    {
                        field: 'img', minWidth: 150, title: '栏目缩略图', align: 'center', templet: function (d) {
                            return '<img class="layui-upload-img" width="200px" src="' + d.img + '">';
                        }
                    },
                    {field: 'sort', minWidth: 80, align: 'center', title: '排序号'},
                    {
                        field: 'status', minWidth: 100, align: 'center', templet: function (d) {
                            if (d.status === 1) {
                                return '<i class="layui-icon layui-icon-ok" style="color: #393;" lay-event="setCategoryStatus"></i>'
                            } else {
                                return '<i class="layui-icon layui-icon-close" style="color: #f00;" lay-event="setCategoryStatus"></i>'
                            }
                        }, title: '状态'
                    },
                    {
                        width: 220, align: 'left', title: '操作', templet: function (d) {
                            let s = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">修改</a>\n' +
                                '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>';
                            return s;
                        }
                    }
                ]],
                done: function () {
                    layer.closeAll('loading');
                }
            });
        }

        $('#btn-expand').click(function () {
            treetable.expandAll('#category-table');
        });

        $('#btn-fold').click(function () {
            treetable.foldAll('#category-table');
        });

        $('#addTopCategory').click(function () {
            var index = layer.open({
                title: '添加顶级分类',
                type: 2,
                shade: 0.2,
                maxmin:true,
                shadeClose: true,
                area: ['100%', '100%'],
                content: '/admin/productCategory/edit',
            });
            $(window).on("resize", function () {
                layer.full(index);
            });
        })

        table.on('tool(category-table)', function (obj) {
            var data = obj.data;

            if (obj.event === 'edit') {
                var index = layer.open({
                    title: '编辑分类',
                    type: 2,
                    shade: 0.2,
                    maxmin:true,
                    shadeClose: true,
                    area: ['100%', '100%'],
                    content: '/admin/productCategory/edit?category_id=' + data.category_id,
                });
                $(window).on("resize", function () {
                    layer.full(index);
                });
                return false;

            } else if (obj.event === 'delete') {
                layer.confirm('确认删除？删除后不能恢复。', function (index) {
                    $.ajax({
                        url: "/admin/productCategory/setProductCategoryStatus?_token={{ csrf_token() }}",
                        type: "post",
                        dataType: "json",
                        data: {
                            "category_id": data.category_id,
                            "status": 3
                        },
                        success: function(result){
                            if(result.code === 0){
                                location.reload()
                                layer.close(index);
                            }else{
                                layer.msg(result.message, {time: 2000});
                            }
                        }
                    });
                    return false;
                });
            } else if (obj.event === 'addChildCategory') {
                var index = layer.open({
                    title: '编辑分类',
                    type: 2,
                    shade: 0.2,
                    maxmin:true,
                    shadeClose: true,
                    area: ['100%', '100%'],
                    content: '/admin/productCategory/edit?parent_id=' + data.category_id,
                });
                $(window).on("resize", function () {
                    layer.full(index);
                });
                return false;

            } else if (obj.event === 'setCategoryStatus') {
                var status = data.status === 1 ? 2 : 1;
                $.ajax({
                    url: "/admin/productCategory/setProductCategoryStatus?_token={{ csrf_token() }}",
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
            }
        });

    });

</script>

</body>
</html>
