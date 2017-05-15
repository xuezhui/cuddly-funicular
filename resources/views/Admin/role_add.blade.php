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
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<!--/meta 作为公共模版分离出去-->

<title>新建网站角色 - 管理员管理</title>
</head>
<body>
<article class="cl pd-20">
	<form action="/admin/addroleDo" method="post" class="form form-horizontal" id="form-admin-role-add">
            <input type="hidden"value="{{@$detail->id}}"  id="id" name="id">
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>角色名称：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="{{@$detail->name}}" placeholder="" id="name" name="name" datatype="*4-16" nullmsg="用户账户不能为空">
			</div>
		</div>
		
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3">角色权限：</label>
			<div class="formControls col-xs-8 col-sm-9">
                            @if($permission_list_arr)
                            @foreach($permission_list_arr as $key=>$permission_list)
				<dl class="permission-list">
                                    <dt>
                                        <label>
                                            <input type="checkbox" value="{{$key}}">
                                        {{$menu[$key]}}</label>
                                    </dt>
                                    <dd>
                                        <dl class="cl permission-list2">                                                    
                                            <dd style="margin-left: 0">
                                                @foreach($permission_list as $k=>$list)
                                                <label class="">
                                                        <input type="checkbox" value="{{$list['id']}}" @if(@in_array(@$list['id'],$role_permission)) checked="checked" @endif name="permission[]">
                                                        {{$list['name']}}
                                                </label>@if(($k+1)%3==0 )<br>@endif
                                                @endforeach
                                            </dd>
                                        </dl>
                                    </dd>
				</dl>
                            @endforeach
                            @endif
			</div>
		</div>

                <div class="row cl">
			<label class="form-label col-xs-4 col-sm-3">描述：</label>
			<div class="formControls col-xs-8 col-sm-9">
                            <textarea name="description" cols="" rows="" class="textarea"  placeholder="说点什么...100个字符以内" dragonfly="true" onKeyUp="textarealength(this,100)">{{@$detail->description}}</textarea>
                            
			</div>
		</div>
		<div class="row cl">
			<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
				<button type="submit" class="btn btn-success radius" id="admin-role-save" name="admin-role-save"><i class="icon-ok"></i> 确定</button>
			</div>
		</div>
	</form>
</article>

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
$(function(){
	$(".permission-list dt input:checkbox").click(function(){
		$(this).closest("dl").find("dd input:checkbox").prop("checked",$(this).prop("checked"));
	});
	$(".permission-list2 dd input:checkbox").click(function(){
		var l =$(this).parent().parent().find("input:checked").length;
		var l2=$(this).parents(".permission-list").find(".permission-list2 dd").find("input:checked").length;
		if($(this).prop("checked")){
			$(this).closest("dl").find("dt input:checkbox").prop("checked",true);
			$(this).parents(".permission-list").find("dt").first().find("input:checkbox").prop("checked",true);
		}
		else{
			if(l==0){
				$(this).closest("dl").find("dt input:checkbox").prop("checked",false);
			}
			if(l2==0){
				$(this).parents(".permission-list").find("dt").first().find("input:checkbox").prop("checked",false);
			}
		}
	});
	
	$("#form-admin-role-add").validate({
		rules:{
			name:{
				required:true,
			},
		},
		onkeyup:false,
		focusCleanup:true,
		success:"valid",
		submitHandler:function(form){
			$(form).ajaxSubmit({
                            dataType:'json',
                            success:function(rs){
                                if(typeof(rs) == 'undefined')
                                    return false;
                                if(rs.errorCode==0){
                                    parent.location.href="/admin/role";return;
//                                    var index = parent.layer.getFrameIndex(window.name);
//                                    parent.$('.btn-refresh').click();
//                                    parent.layer.close(index);
                                    
                                }else
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