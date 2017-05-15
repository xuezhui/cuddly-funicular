@extends('Admin.layouts.pop')

@section('content')
<title>审核</title>
</head>
<body>
	<div class="page-container">
		<form class="form form-horizontal" id="form" action="/admin/finance/swithdrawdeal" method="post">
			<input name="id" value="@if($apply){{ $apply->id }}@endif" type="hidden">
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">服务商：</label>
				<div class="formControls col-xs-8 col-sm-9">
					{{$apply->owner}}
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">金额：</label>
				<div class="formControls col-xs-8 col-sm-9">
					{{$apply->amount}}
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">审核结果：</label>
				<div class="formControls col-xs-8 col-sm-9 skin-minimal">
					<div class="radio-box">
						<input name="status" type="radio" id="sex-1" value=0 @if($apply->apply_progress==0) checked @endif>
						<label for="sex-1">冻结</label>
					</div>
					<div class="radio-box">
						<input name="status" type="radio" id="sex-2" value=1 @if($apply->apply_progress==1) checked @endif>
						<label for="sex-2">暂不审核</label>
					</div>
					<div class="radio-box">
						<input name="status" type="radio" id="sex-2" value=2 @if($apply->apply_progress==2) checked @endif>
						<label for="sex-2">审核通过</label>
					</div>
					<div class="radio-box">
						<input name="status" type="radio" id="sex-3" value=3 @if($apply->apply_progress==3) checked @endif>
						<label for="sex-3">审核不通过</label>
					</div>
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
<script src="/js/uploads.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
	$(function(){
		$('.skin-minimal input').iCheck({
			checkboxClass: 'icheckbox-blue',
			radioClass: 'iradio-blue',
			increaseArea: '20%'
		});
		$.Huitab("#tab-system .tabBar span","#tab-system .tabCon","current","click","0");
	});
	$("#form").validate({

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
				}else{
					layer.msg(result.errorStr,{icon:0,time:1000});
				}
			},'dataType':'json'});
			
		}
	});
</script>
@endsection
