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
        <script type="text/javascript" src="lib/html5.js"></script>
        <script type="text/javascript" src="lib/respond.min.js"></script>
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css" />
        <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css" />
        <link rel="stylesheet" type="text/css" href="/lib/Hui-iconfont/1.0.8/iconfont.css" />
        <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin" />
        <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/style.css" />
        <!--[if IE 6]>
        <script type="text/javascript" src="http://lib.h-ui.net/DD_belatedPNG_0.0.8a-min.js" ></script>
        <script>DD_belatedPNG.fix('*');</script><![endif]-->
        <!--/meta 作为公共模版分离出去-->

        <title>栏目设置</title>
    </head>
    <body>

        <section class="Hui-article-box">
            <nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 系统管理 <span class="c-gray en">&gt;</span> 栏目管理 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
            <div class="pd-20">
                <form action="/admin/addsysmenuDo" method="post" class="form form-horizontal" id="form-menu-add">
                    <div id="tab-category" class="HuiTab">
                        <div class="tabBar cl"><span>基本设置</span></div>
                        <div class="tabCon">
                            <!--					<div class="row cl">
                                                                            <label class="form-label col-xs-4 col-sm-3">栏目ID：</label>
                                                                            <div class="formControls col-xs-8 col-sm-9">11230</div>
                                                                    </div>-->
                            <input type="hidden" value="{{@$detail->id}}" name="id">
                            <div class="row cl">
                                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>上级菜单：</label>
                                <div class="formControls col-xs-8 col-sm-9"> <span class="select-box">
                                        <select class="select" id="sel_Sub" name="parentID">
                                            <option value="0">顶级分类</option>
                                            @foreach($menulist as $key=>$menu)
                                            <option @if(@$detail->id==$menu->id || @$detail->parentID == $menu->id) selected="selected" @endif value="{{$menu->id}}">{{$menu->name}}</option>
                                            @endforeach
                                            <!--								<option value="101">&nbsp;&nbsp;├ 分类二级</option>
                                                                                                            <option value="102">&nbsp;&nbsp;├ 分类二级</option>
                                                                                                            <option value="20">分类一级</option>
                                                                                                            <option value="200">&nbsp;&nbsp;├ 分类二级</option>-->
                                        </select>
                                    </span> </div>
                                <div class="col-3"> </div>
                            </div>
                            <div class="row cl">
                                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>菜单名称：</label>
                                <div class="formControls col-xs-8 col-sm-9">
                                    <input type="text" class="input-text" value="{{@$detail->name}}" placeholder="菜单名称" id="name" name="name">
                                </div>
                                <div class="col-3"> </div>
                            </div>
                            <div class="row cl">
                                <label class="form-label col-xs-4 col-sm-3">排序：</label>
                                <div class="formControls col-xs-8 col-sm-9">
                                    <input type="text" class="input-text" value="{{@$detail->sort}}" placeholder="菜单排序：值越大越靠前" id="sort" name="sort">
                                </div>
                                <div class="col-3"> </div>
                            </div>
                            <div class="row cl">
                                <label class="form-label col-xs-4 col-sm-3">链接：</label>
                                <div class="formControls col-xs-8 col-sm-9">
                                    <input type="text" class="input-text" value="{{@$detail->url}}" placeholder="菜单链接" id="url" name="url">
                                </div>
                                <div class="col-3"> </div>
                            </div>

                            <div class="row cl">
                                <label class="form-label col-xs-4 col-sm-3">icon：</label>
                                <div class="formControls col-xs-8 col-sm-9">
                                    <input type="text" class="input-text" value="{{@$detail->icon}}" placeholder="菜单图标" id="icon" name="icon">
                                </div>
                                <div class="col-3"> </div>
                            </div>
                        </div>


                    </div>
                    <div class="row cl">
                        <div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
                            <input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
                        </div>
                    </div>
                </form>
            </div>
        </section>
        <!--_footer 作为公共模版分离出去--> 
        <script type="text/javascript" src="/lib/jquery/1.9.1/jquery.min.js"></script> 
        <script type="text/javascript" src="/lib/layer/2.4/layer.js"></script>
        <script type="text/javascript" src="/static/h-ui/js/H-ui.js"></script> 
        <script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.page.js"></script> 
        <!--/_footer /作为公共模版分离出去--> 

        <!--请在下方写此页面业务相关的脚本--> 
        <script type="text/javascript" src="/lib/jquery.validation/1.14.0/jquery.validate.js"></script> 
        <script type="text/javascript" src="/lib/jquery.validation/1.14.0/validate-methods.js"></script> 
        <script type="text/javascript" src="/lib/jquery.validation/1.14.0/messages_zh.js"></script> 
        <script type="text/javascript">
$(function () {
    $('.skin-minimal input').iCheck({
        checkboxClass: 'icheckbox-blue',
        radioClass: 'iradio-blue',
        increaseArea: '20%'
    });

    $.Huitab("#tab-category .tabBar span", "#tab-category .tabCon", "current", "click", "0");
    $("#form-menu-add").validate({
        rules: {
            name: {
                required: true,
            }
            
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function (form) {
            $(form).ajaxSubmit({
                dataType: 'json',
                success: function (rs) {
                    if (typeof (rs) == 'undefined')
                        return false;
                    if (rs.errorCode == 0) {
                        parent.location.href = "/admin/sysmenu";
                        return;
                    } else
                        layer.msg('执行失败');
                }
            });

        }
    });

});
        </script> 
        <!--/请在上方写此页面业务相关的脚本-->
    </body>
</html>