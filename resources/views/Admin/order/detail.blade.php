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
    <script type="text/javascript" src="{{URL::asset('lib/html5.js')}}"></script>
    <script type="text/javascript" src="{{URL::asset('lib/respond.min.js')}}"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="{{URL::asset('static/h-ui/css/H-ui.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{URL::asset('static/h-ui.admin/css/H-ui.admin.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{URL::asset('lib/Hui-iconfont/1.0.8/iconfont.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{URL::asset('static/h-ui.admin/skin/default/skin.css')}}" id="skin" />
    <link rel="stylesheet" type="text/css" href="{{URL::asset('static/h-ui.admin/css/style.css')}}" />
    <!--[if IE 6]>
    <script type="text/javascript" src="http://lib.h-ui.net/DD_belatedPNG_0.0.8a-min.js" ></script>
    <script>DD_belatedPNG.fix('*');</script><![endif]-->
    <!--/meta 作为公共模版分离出去-->

    <title>订单详情</title>
    <meta name="keywords" content="H-ui.admin v3.0,H-ui网站后台模版,后台模版下载,后台管理系统模版,HTML后台模版下载">
    <meta name="description" content="H-ui.admin v3.0，是一款由国人开发的轻量级扁平化网站后台模板，完全免费开源的网站后台管理系统模版，适合中小型CMS后台系统。">
</head>
<body>

<article class="cl pd-20">
    <table class="table table-border table-bordered table-hover table-bg table-sort">
        <thead>
        <tr class="text-c">
            <th width="120">产品</th>
            <th width="60">规格</th>
            <th width="40">购买数量</th>
        </tr>
        </thead>
        <tbody>

        @foreach ($list as $vo)
            <tr class="text-c">
                <td><img src="{{$vo->photos}}" alt="" width="100px" height="100px"> {{$vo->name}} </td>
                <td>@if($vo->spec){{$vo->spec->name}}@endif</td>
                <td>{{$vo->pro_num}}</td>
                
            </tr>
        @endforeach
        </tbody>
    </table>
    <form action="" method="post" class="form form-horizontal" id="form-member-add">
       
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3">客户姓名：</label>
            <div class="formControls col-xs-8 col-sm-9">
                {{$detail->realname}}
            </div>
        </div>

        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3">手机：</label>
            <div class="formControls col-xs-8 col-sm-9">
                {{$detail->mobile}}
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3">邮箱：</label>
            <div class="formControls col-xs-8 col-sm-9">
               {{$detail->email}}
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3">下单时间：</label>
            <div class="formControls col-xs-8 col-sm-9">
                {{$detail->created_at}}
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3">订单总额：</label>
            <div class="formControls col-xs-8 col-sm-9">
                {{$detail->total_amount}}
            </div>
        </div>
        @if($detail->logistics_status>=1)
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3">发货时间：</label>
            <div class="formControls col-xs-8 col-sm-9">
                {{$detail->delivery_at}}
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3">快递：</label>
            <div class="formControls col-xs-8 col-sm-9">
                {{$detail->logistics_type}}/{{$detail->logistics_number}}
            </div>
        </div>
        @endif
        @if($detail->logistics_status==2)
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3">完成时间：</label>
            <div class="formControls col-xs-8 col-sm-9">
                {{$detail->finished_at}}
            </div>
        </div>
        @endif


    </form>
</article>

<!--_footer 作为公共模版分离出去-->
<script type="text/javascript" src="/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="/static/h-ui/js/H-ui.js"></script>
<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.page.js"></script>
<!--/_footer /作为公共模版分离出去-->

<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>