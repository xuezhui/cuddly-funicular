<!--_meta 作为公共模版分离出去-->
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="renderer" content="webkit|ie-comp|ie-stand">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
        <meta http-equiv="Cache-Control" content="no-siteapp" />
        <link rel="Bookmark" href="favicon.ico" >
        <link rel="Shortcut Icon" href="favicon.ico" />
        <!--[if lt IE 9]>
        <script type="text/javascript" src="/lib/html5.js"></script>
        <script type="text/javascript" src="/lib/respond.min.js"></script>
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css" />
        <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css" />
        <link rel="stylesheet" type="text/css" href="/lib/Hui-iconfont/1.0.8/iconfont.css" />
        <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin" />
        <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/style.css" />
        <link rel="stylesheet" href="/static/pagination.css">
        @yield('my-css')
        <script type="text/javascript" src="/lib/jquery/1.9.1/jquery.min.js"></script> 
        <!--[if IE 6]>
        <script type="text/javascript" src="http://lib.h-ui.net/DD_belatedPNG_0.0.8a-min.js" ></script>
        <script>DD_belatedPNG.fix('*');</script>
        <![endif]-->
        <!--/meta 作为公共模版分离出去-->

        <title>管理后台</title>
        <meta name="keywords" content="">
        <meta name="description" content="">
    </head>
    <body>
        <!--_header 作为公共模版分离出去-->
        @extends('Admin/layouts/header')
        <!--/_header 作为公共模版分离出去-->

        <!--_menu 作为公共模版分离出去-->

        @extends('Admin.layouts.menu')

        <!--/_menu 作为公共模版分离出去-->

        @yield('content')



        <!--_footer 作为公共模版分离出去-->
        
        <script type="text/javascript" src="/lib/layer/2.4/layer.js"></script>
        <script type="text/javascript" src="/static/h-ui/js/H-ui.js"></script> 
        <script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.page.js"></script> 
        <!--/_footer /作为公共模版分离出去-->

    </body>
</html>