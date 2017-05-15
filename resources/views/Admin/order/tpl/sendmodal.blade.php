@extends('Admin.layouts.pop')

@section('content')

<title>发货</title>
</head>
<body>
	<div class="page-container">
		<form class="form form-horizontal" id="form" action="/admin/order/confirmsend" method="post">
			<input name="id" value="{{$order->id}}" type="hidden">
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">快递公司：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<select class="input-text" name="type">
						@foreach($coms as $key=>$com)
							<option value="{{$key}}" @if($order->logistics_type==$key)selected @endif>{{$com}}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">快递单号：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input name="number" class="input-text" value="@if($order->logistics_number){{$order->logistics_number}}@endif">
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
		rules:{
			type:{
				required:true,
			},
			number:{
				required:true,
			},
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
				}else{
					layer.msg(result.errorStr,{icon:0,time:1000});
				}
			},'dataType':'json'});
			
		}
	});
</script>
@endsection
