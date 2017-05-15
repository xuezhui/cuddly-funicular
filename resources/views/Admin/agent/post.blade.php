@extends('Admin.layouts.pop')

@section('content')

<title>新增代理人</title>
</head>
<body>
	<div class="page-container">
		<form class="form form-horizontal" id="form" action="/admin/agentpost" method="post">
			<input name="id" value="@if($agent){{ $agent->id }}@endif" type="hidden">

			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>代理人：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input name="agentname" class="input-text" value="@if($agent){{ $agent->agentname }}@endif" @if($agent)readonly @endif>
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>代理人地址：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input name="agentaddr" class="input-text" value="@if($agent){{ $agent->agentaddr }}@endif">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>手机号：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input name="phone" type="number" class="input-text" @if($agent)value="{{ $agent->phone }}" readonly @endif style="width:100%;">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">@if(!$agent)<span class="c-red">*</span>@endif 登录密码：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input type="password" name="password" class="input-text" value="" id="pwd">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">@if(!$agent)<span class="c-red">*</span>@endif 确认密码：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input type="password" name="repassword" class="input-text" value="" >
				</div>
			</div>
			@if(!$agent)
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>角色：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<select name="role" class="input-text">
						@foreach($roles as $r)
						<option value="{{$r->id}}" @if($role&&$role->role_id==$r->id)selected @endif>{{$r->name}}</option>
						@endforeach
					</select>
				</div>
			</div>
			@endif
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-3">备注：</label>
				<div class="formControls col-xs-8 col-sm-9">
                    <textarea name="note" cols="" rows="" class="textarea"  placeholder="说点什么...100个字符以内" dragonfly="true" maxlength="100"></textarea>
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
			agentname:{
				required:true,
			},
			agentaddr:{
				required:true,
			},
			phone:{
				required:true,
				number:true,
				maxlength:11,
				minlength:11
			},
			@if(!$agent)
			password:{
				required:true,
				minlength:6,
			},
			@endif
			repassword:{
				equalTo:"#pwd",
			}
			
		},
		messages:{
			phone:'请输入正确的手机号',
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
					layer.msg('请勿输入重复数据',{icon:0,time:1000});
				}
			},'dataType':'json'});
			
		}
	});
</script>
@endsection
