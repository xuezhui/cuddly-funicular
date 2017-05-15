@extends('Admin.layouts.pop')
@section('content')
<article class="cl pd-20">
	<form method="post" action="/admin/member/editResult" class="form form-horizontal" id="form-member-edit">
		<input type="hidden" value="{{$member->id}}" placeholder="" id="id" name="id">
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>用户名：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="{{$member->realname}}" placeholder="" id="realname" name="realname">
			</div>
		</div>
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>昵称：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="{{$member->nickname}}" placeholder="" id="nickname" name="nickname">
			</div>
		</div>
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>邮箱：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="{{$member->email}}" placeholder="@" name="email" id="email">
			</div>
		</div>
		<div class="row cl">
			<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
				<input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
			</div>
		</div>
	</form>
</article>
@section('my-js')
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/jquery.validate.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/validate-methods.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/messages_zh.js"></script>
@endsection
<script type="text/javascript">
$(function(){
	
	$("#form-member-edit").validate({
		rules:{
            realname:{
				minlength:2,
				maxlength:16
			},
            nickname:{
                minlength:2,
                maxlength:16
            },
			email:{
				email:true,
			},

		},
		onkeyup:false,
		focusCleanup:true,
		success:"valid",
		submitHandler:function(form){
			$(form).ajaxSubmit(function(){
                var index = parent.layer.getFrameIndex(window.name);
                parent.location.reload();
                parent.layer.close(index);
			});

		}
	});
});
</script> 
<!--/请在上方写此页面业务相关的脚本-->
@endsection
</html>