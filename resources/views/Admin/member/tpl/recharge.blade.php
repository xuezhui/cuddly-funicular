@extends('Admin.layouts.pop')
@section('content')
<article class="cl pd-20">
	<form method="post" action="/admin/member/rechargeResult" class="form form-horizontal" id="form-member-recharge">
		<input type="hidden" value="{{$member->id}}" placeholder="" id="id" name="id">
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>手机号：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="{{$member->telephone}}" placeholder="" id="telephone" name="telephone" readonly="true">
			</div>
		</div>
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>充值金额（元）：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="" placeholder="" id="money" name="money">
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
	$("#form-member-recharge").validate({
		rules:{
		    telephone:{

			},
            money:{
                required:true,
                number:true,
				min:1,
			},
		},
		onkeyup:false,
		focusCleanup:true,
		success:"valid",
        submitHandler:function(form){
            layer.confirm('确认充值吗？',function (index) {
                $(form).ajaxSubmit(function(){
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.location.reload();
                    parent.layer.close(index);
                });
            })
        }
	});
});
</script> 
@endsection
</html>