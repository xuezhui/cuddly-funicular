@extends('Admin.layouts.pop')
@section('content')
<article class="cl pd-20">
	<form method="post" action="/admin/smember/toAudit" class="form form-horizontal" id="form-smember-audit">
		<input type="hidden" value="{{$member->id}}" placeholder="" id="id" name="id">
		<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>审核状态：</label>
		<div class="mt-20 skin-minimal">
			@foreach($apply_progress as $key => $item)
			<div class="radio-box">
				<input type="radio" id="radio-{{$key}}" value="{{$key}}" @if($member->apply_progress == $key) checked="checked" @endif name="apply_progress">
				<label for="radio-{{$key}}">{{$item}}</label>
			</div>
			@endforeach
		</div>
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>审核备注：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="{{$member->apply_note}}" placeholder="" name="apply_note">
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
    //checkbox美化 http://www.bootcss.com/p/icheck/
	$('.skin-minimal input').iCheck({
		checkboxClass: 'icheckbox-blue',
		radioClass: 'iradio-blue',
		increaseArea: '20%'
	});

    $("#form-smember-audit").validate({
        rules:{
            apply_progress:{
                required:true,
                range:[0,3]
            }
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