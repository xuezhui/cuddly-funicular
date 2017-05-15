@extends('Admin.layouts.pop')

@section('content')
<style type="text/css" media="screen">
	.choose{
		position: relative;
	}
	#upload{
		position: absolute;
		left: 0px;
		top:0px;
		opacity: 0;
		z-index: 1;
		width: 100%;
	}
	.img-thumbnail{
	    display: block;
	    max-width: 100%;
	    height: auto;
	    padding: 4px;
	    line-height: 1.42857143;
	    background-color: #fff;
	    border: 1px solid #ddd;
	    border-radius: 4px;
	    -webkit-transition: all .2s ease-in-out;
	    -o-transition: all .2s ease-in-out;
	    transition: all .2s ease-in-out;
	    width: 140px;
	    height: 140px;
	    margin:5px;
	}
</style>
<title>新增幻灯片</title>
</head>
<body>
	<div class="page-container">
		<form class="form form-horizontal" id="form" action="/admin/filmslide/post" method="post">
			<input name="id" value="@if($slide){{ $slide->id }}@endif" type="hidden">
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">别名：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input name="title" class="input-text" value="@if($slide){{ $slide->title }}@endif">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">排序：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input name="displayorder" class="input-text" value="@if($slide){{ $slide->displayorder }}@endif">
					<span class="help-block">数字越小排序越靠前，默认按时间排序</span>
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>幻灯片：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<div class="uploader-thum-container">
						<input name="photos" class="input-text" id="photos" value="@if($slide){{$slide->photos}}@endif" readonly>
						<span class="choose">
							<div id="filePicker" class=" btn btn-primary radius">选择图片</div>
							<input type="file" id="upload">
						</span>
						
						<span id="btn-star" class="btn btn-default btn-uploadstar radius ml-10">开始上传</span>
						<img src="@if($slide){{$slide->photos}}@endif" alt="" id="img" class="img-thumbnail">
					</div>
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">链接：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input name="link" class="input-text" value="@if($slide){{ $slide->link }}@endif">
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>是否启用：</label>
				<div class="formControls col-xs-8 col-sm-9 skin-minimal">
					<div class="radio-box">
						<input name="isable" type="radio" id="sex-1" @if(!$slide||!$slide->isable)checked @endif value="0">
						<label for="sex-1">不启用</label>
					</div>
					<div class="radio-box">
						<input type="radio" id="sex-2" name="isable" @if($slide&&$slide->isable)checked @endif value="1">
						<label for="sex-2">启用</label>
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
	$("#upload").change(function(){
		if(window.FileReader){
            var oFReader = new FileReader();
            var file = this.files[0];
            //如果要限定上传文件的类型，可以通过文件选择器获取文件对象并通过type属性来检查文件类型
            var sReg = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;

            if(!sReg.test(file.type)){
                layer.msg('只允许上传图片文件!',{icon:0,time:1000});
            }
            oFReader.onloadend = function(e) {
                document.getElementById("img").src = e.target.result;
            };

            oFReader.readAsDataURL(file);
        }
	});
	$("#btn-star").click(function(){
		var src = document.getElementById("img").src;
		if($.trim(src)==""){
			layer.msg('请上传资源!',{icon:0,time:1000});
		}
		var file = $("#upload")[0];
		if(file.files[0]!=undefined){
			_up(file,'{{ route('admin.util.uploadfile') }}',$("input[name=photos]"));
		}
		
	})
	$("#form").validate({
		rules:{
			photos:{
				required:true,
			},
			isable:{
				required:true,
			},
		},
		messages:{
			photos:'请上传幻灯片',
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
