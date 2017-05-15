@extends('Admin.layouts.pop')

@section('content')
	<div class="Hui-article">
		<article class="cl pd-20">
			<form action="/admin/activity/post" method="post" class="form form-horizontal" id="form">
				<div id="tab-system" class="HuiTab">
					<div class="tabBar cl">
						<span class="first">基本信息</span></div>
					<div class="tabCon">
						@include('Admin.activity.tpl.base')
					</div>
				</div>
				<div class="row cl">
					<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-2">
						<button  class="btn btn-primary radius" type="submit"><i class="Hui-iconfont">&#xe632;</i> 保存</button>
						<button onClick="layer_close();" class="btn btn-default radius" type="button">&nbsp;&nbsp;取消&nbsp;&nbsp;</button>
					</div>
				</div>
			</form>
		</article>
	</div>
	<script type="text/javascript" src="/lib/jquery.validation/1.14.0/jquery.validate.js"></script>
	<script type="text/javascript" src="/lib/jquery.validation/1.14.0/validate-methods.js"></script>
	<script type="text/javascript" src="/lib/jquery.validation/1.14.0/messages_zh.js"></script>
	<script type="text/javascript" src="/lib/ueditor/1.4.3/ueditor.config.js"></script>
	<script type="text/javascript" src="/lib/ueditor/1.4.3/ueditor.all.min.js"> </script>
	<script type="text/javascript" src="/lib/ueditor/1.4.3/lang/zh-cn/zh-cn.js"></script>
	<script type="text/javascript">

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
				name:{
					required:true,
					minlength:2,
					maxlength:16
				},
				isable:{
					required:true,
				},
				"desc[]":{
					required:true,
				}
			},
			messages:{
				name:'请填写专题名',
				photos:'请上传缩略图片',
				"desc[]":'请上传具体内容',
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
					}
				},'dataType':'json'});

			}
		});
	</script>
@endsection