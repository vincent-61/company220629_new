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
                                <input type="hidden" name="category_id" id="category_id">
                                <input type="text" name="category_name" placeholder="请选择分类" autocomplete="off" class="layui-input" id="category_name_select" value="" readonly="true">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">产品名称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="product_name" autocomplete="off" class="layui-input">
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
                <button class="layui-btn layui-btn-danger layui-btn-sm data-del-btn" lay-event="delBatch"> 批量删除 </button>
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
            url: '/admin/product/getProductList?_token={{ csrf_token() }}',
            method: 'post',
            toolbar: '#toolbarDemo',
            defaultToolbar: ['filter'],
            cols: [[
                {type: "checkbox", width: 50},
                {field: 'category_name', minWidth: 120, title: '所属分类'},
                {field: 'product_name', minWidth: 120, title: '产品名称'},
                {
                    field: 'img', width: 150, title: '产品图片', align: 'center', templet: function (d) {
                        return '<img class="layui-upload-img" width="200px" src="' + d.img_190 + '">';
                    }
                },

                {field: 'sort', width: 80, title: '排序'},
                {
                    field: 'recommend', minWidth: 80, align: "center", templet: function (d) {
                        if (d.recommend === 2) {
                            return '<span class="layui-badge layui-bg-green" lay-event="setProductRecommend">推荐</span>';
                        } else if (d.recommend === 1) {
                            return '<span class="layui-badge-rim" lay-event="setProductRecommend">不推荐</span>'
                        }
                    }, title: '是否推荐'
                },
                {
                    field: 'status', minWidth: 80, align: "center", templet: function (d) {
                        if (d.status === 1) {
                            return '<i class="layui-icon layui-icon-ok" style="color: #393;" lay-event="setProductStatus"></i>';
                        } else if (d.status === 2) {
                            return '<i class="layui-icon layui-icon-close" style="color: #f00;" lay-event="setProductStatus"></i>'
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
                    'category_id': data.field.category_id,
                    'product_name': data.field.product_name,
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
                    title: '添加产品',
                    type: 2,
                    shade: 0.2,
                    maxmin:true,
                    shadeClose: true,
                    area: ['100%', '100%'],
                    content: '/admin/product/edit',
                });
                $(window).on("resize", function () {
                    layer.full(index);
                });
            } else if (obj.event === 'delBatch') {
                var checkStatus = table.checkStatus('currentTableId')
                    , data = checkStatus.data;
                var newData = data.map(function(value,index) {
                    return value.product_id;
                })

                if (checkStatus.data.length >= 1) {
                    layer.confirm('确认批量删除所选商品？删除后无法恢复',{
                        icon: 3,
                        skin: 'layer-ext-moon'
                    },function(){
                        $.ajax({
                            url: "/admin/product/doDelBatch?_token={{ csrf_token() }}",
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
                    title: '编辑产品',
                    type: 2,
                    shade: 0.2,
                    maxmin:true,
                    shadeClose: true,
                    area: ['100%', '100%'],
                    content: '/admin/product/edit?product_id=' + data.product_id,
                });
                $(window).on("resize", function () {
                    layer.full(index);
                });
                return false;

            } else if (obj.event === 'setProductStatus') {

                var status = data.status === 1 ? 2 : 1;
                $.ajax({
                    url: "/admin/product/setProductStatus?_token={{ csrf_token() }}",
                    type: "post",
                    dataType: "json",
                    data: {
                        "product_id": data.product_id,
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
            } else if (obj.event === 'setProductRecommend') {

                var recommend = data.recommend === 1 ? 2 : 1;
                $.ajax({
                    url: "/admin/product/setProductRecommend?_token={{ csrf_token() }}",
                    type: "post",
                    dataType: "json",
                    data: {
                        "product_id": data.product_id,
                        "recommend": recommend
                    },
                    success: function(result){
                        if(result.code === 0){
                            obj.update({
                                recommend: recommend
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
                        url: "/admin/product/setProductStatus?_token={{ csrf_token() }}",
                        type: "post",
                        dataType: "json",
                        data: {
                            "product_id": data.product_id,
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
            } else if (obj.event === 'editImg') {
                var editImg = layer.open({
                    title: '编辑图片',
                    type: 2,
                    shade: 0.2,
                    maxmin:true,
                    shadeClose: true,
                    area: ['100%', '100%'],
                    content: '/admin/product/editImg?product_id=' + data.product_id,
                });
                $(window).on("resize", function () {
                    layer.full(editImg);
                });
                return false;
            }
        });

        tableSelect.render({
            elem: '#category_name_select',
            checkedKey: 'category_name_select',
            searchType: 'one',
            searchKey: 'category_name',
            searchPlaceholder: '请输入分类名称',
            table: {
                url: '/admin/productCategory/getProductCategoryList?_token={{ csrf_token() }}',
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

    });

</script>

</body>
</html>
