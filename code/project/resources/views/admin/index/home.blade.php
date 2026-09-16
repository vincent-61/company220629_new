<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>首页二</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/layui-v2.6.8/css/layui.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}lib/font-awesome-4.7.0/css/font-awesome.min.css" media="all">
    <link rel="stylesheet" href="{{ $staticAdminUrl }}css/public.css" media="all">
</head>
<body>

<style>
    .welcome .layui-card {
        border: 1px solid #f2f2f2;
        border-radius: 5px;
    }

    .welcome .icon {
        margin-right: 10px;
        color: #1aa094;
    }

    .welcome .icon-cray {
        color: #ffb800 !important;
    }

    .welcome .icon-blue {
        color: #1e9fff !important;
    }

    .welcome .icon-tip {
        color: #ff5722 !important;
    }

    .welcome .layuimini-qiuck-module {
        text-align: center;
        margin-top: 10px
    }

    .welcome .layuimini-qiuck-module a i {
        display: inline-block;
        width: 100%;
        height: 60px;
        line-height: 60px;
        text-align: center;
        border-radius: 2px;
        font-size: 30px;
        background-color: #F8F8F8;
        color: #333;
        transition: all .3s;
        -webkit-transition: all .3s;
    }

    .welcome .layuimini-qiuck-module a cite {
        position: relative;
        top: 2px;
        display: block;
        color: #666;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        font-size: 14px;
    }

    .welcome .welcome-module {
        width: 100%;
        height: 210px;
    }

    .welcome .panel {
        background-color: #fff;
        border: 1px solid transparent;
        border-radius: 3px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
        box-shadow: 0 1px 1px rgba(0, 0, 0, .05)
    }

    .welcome .panel-body {
        padding: 10px
    }

    .welcome .panel-title {
        margin-top: 0;
        margin-bottom: 0;
        font-size: 12px;
        color: inherit
    }

    .welcome .label {
        display: inline;
        padding: .2em .6em .3em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: .25em;
        margin-top: .3em;
    }

    .welcome .layui-red {
        color: red
    }

    .welcome .main_btn > p {
        height: 40px;
    }

    .welcome .layui-bg-number {
        background-color: #F8F8F8;
    }

    .welcome .layuimini-notice:hover {
        background: #f6f6f6;
    }

    .welcome .layuimini-notice {
        padding: 7px 16px;
        clear: both;
        font-size: 12px !important;
        cursor: pointer;
        position: relative;
        transition: background 0.2s ease-in-out;
    }

    .welcome .layuimini-notice-title, .layuimini-notice-label {
        padding-right: 70px !important;
        text-overflow: ellipsis !important;
        overflow: hidden !important;
        white-space: nowrap !important;
    }

    .welcome .layuimini-notice-title {
        line-height: 28px;
        font-size: 14px;
    }

    .welcome .layuimini-notice-extra {
        position: absolute;
        top: 50%;
        margin-top: -8px;
        right: 16px;
        display: inline-block;
        height: 16px;
        color: #999;
    }
</style>
<div class="layuimini-container layuimini-page-anim">
    <div class="layuimini-main welcome">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header"><i class="fa fa-fire icon"></i>系统信息</div>
                    <div class="layui-card-body layui-text">
                        <div class="layui-card-body layui-text layadmin-text">
                            <p>您好！{{ $adminInfo['real_name'] }}。您上次的登录时间为：{{ $adminInfo['last_login_time'] }}</p>
                        </div>
                        <table class="layui-table">
                            <colgroup>
                                <col width="150">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <td>前端框架名称</td>
                                <td>
                                    layuimini v2.0.0
                                </td>
                            </tr>
                            <tr>
                                <td>后端框架名称</td>
                                <td>laravel 8</td>
                            </tr>
                            <tr>
                                <td>主要特色</td>
                                <td>响应式 / 清爽 / 极简</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="{{ $staticAdminUrl }}lib/layui-v2.6.8/layui.js" charset="utf-8"></script>
<script src="{{ $staticAdminUrl }}js/lay-config.js?v=1.0.4" charset="utf-8"></script>
<script>
    layui.use(['layer'], function () {
        var $ = layui.jquery;

    });
</script>

</body>
</html>
