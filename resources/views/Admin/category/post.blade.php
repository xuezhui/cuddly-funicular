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
<title>新增类目</title>
</head>
<body>
	<div class="page-container">
		<form class="form form-horizontal" id="form-article-add" >
			<input name="id" value="@if($category){{ $category->id }}@endif" type="hidden">
			@if($pid)
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">一级类目：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<div class="form-control-static">{{ $pid }}</div>
					<input name="pid" class="" value="{{ $pid }}" type="hidden">
				</div>
			</div>
			@endif
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>类目名称：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<input name="categoryName" class="input-text" value="@if($category){{ $category->category_name }}@endif" required>
				</div>
			</div>
			<div class="row cl">
				<label class="form-label col-xs-4 col-sm-2">缩略图：</label>
				<div class="formControls col-xs-8 col-sm-9">
					<div class="uploader-thum-container">
						<input name="photos" class="input-text" id="photos" value="@if($category){{$category->photos}}@endif" readonly>
						<span class="choose">
							<div id="filePicker" class=" btn btn-primary radius">选择图片</div>
							<input type="file" id="upload">
						</span>
						
						<span id="btn-star" class="btn btn-default btn-uploadstar radius ml-10">开始上传</span>
						<img src="@if($category){{$category->photos}}@endif" alt="" id="img" class="img-thumbnail">
					</div>
				</div>
			</div>
			<div class="row cl">
				<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-2">
					<span onClick="submit();" class="btn btn-primary radius" type="submit"><i class="Hui-iconfont">&#xe632;</i> 保存</span>
					<button onClick="layer_close();" class="btn btn-default radius" type="button">&nbsp;&nbsp;取消&nbsp;&nbsp;</button>
				</div>
			</div>
		</form>
	</div>
</body>
<script src="/js/uploads.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
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
	function submit(){
		var name = $("input[name=categoryName]").val();
		if($.trim(name)==""){
			layer.msg('请填写类目名称!',{icon:0,time:1000});
			return false;
		}
		var data = $("form").serialize();
		$.post('{{route('admin.category.post')}}',data,function(result){
			parent.location.reload();
		})
		// layer_close();
	}
</script>
@endsection
