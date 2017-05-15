@extends('Admin.layouts.pop')

@section('content')

<title>新增会员</title>
</head>
<body>
	<div class="page-container">
		<form class="form form-horizontal" id="form" action="/admin/agentmember/post" method="post">
			<input name="id" value="@if($member){{ $member->id }}@endif" type="hidden">

			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>用户名：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input type="text" class="input-text" value="@if($member){{$member->realname}}@endif" placeholder="" id="realname" name="realname">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>昵称：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input type="text" class="input-text" value="@if($member){{$member->nickname}}@endif" placeholder="" id="nickname" name="nickname">
				</div>
			</div>
			
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>手机：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input type="text" class="input-text" value="@if($member){{$member->telephone}}@endif" placeholder="" id="mobile" name="mobile">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-3">@if(!$member)<span class="c-red">*</span>@endif 登录密码：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input type="password" class="input-text" value="" placeholder="" name="password" id="pwd">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-3">@if(!$member)<span class="c-red">*</span>@endif 重复密码：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input type="password" class="input-text" value="" placeholder="" id="" name="repassword">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>邮箱：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input type="text" class="input-text" value="@if($member){{$member->email}}@endif" placeholder="@" name="email" id="email">
				</div>
			</div>
			<div class="row cl">
				<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-2">
					<button class="btn btn-primary radius" type="submit"><i class="Hui-iconfont">&#xe632;</i> 保存</button>
					<button onClick="layer_close();" class="btn btn-default radius" type="button">&nbsp;&nbsp;取消&nbsp;&nbsp;</button>
				</div>
			</div>
		</form>
	</div>
</body>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/jquery.validate.js"></script> 
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/validate-methods.js"></script> 
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/messages_zh.js"></script>
<script type="text/javascript" charset="utf-8">
	
	$("#form").validate({
		rules:{
			realname:{
				required:true,
			},
			nickname:{
				required:true,
			},
			mobile:{
				required:true,
				number:true,
				maxlength:11,
				minlength:11
			},
			@if(!$member)
			password:{
				required:true,
				minlength:6,
			},
			@endif
			repassword:{
				equalTo:"#pwd",
			},
			email:{
				required:true,
				email:true
			}
			
		},
		messages:{
			mobile:'请输入正确的手机号',
		},
		onkeyup:false,
		focusCleanup:true,
		ignore: "",
		success:"valid",
		submitHandler:function(form){
			$(form).ajaxSubmit({success:function(result){
				if(result.errorCode==0){
					var index = parent.layer.getFrameIndex(window.name);
					parent.location.reload();
					parent.layer.close(index);
				}else if(result.errorCode==2){
					layer.msg('请勿输入重复的手机号/邮箱',{icon:0,time:1000});
				}
			},'dataType':'json'});
			
		}
	});
</script>
@endsection
