@extends('Admin.layouts.pop')
@section('my-css')
    <link rel="stylesheet" type="text/css" href="/lib/editor.md/css/editormd.css" />
    <style type="text/css" media="screen">
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
        #slider-3 {
            width: 400px;
            height: 300px;
            margin-left: 25%;
        }
        .slider{position:relative;text-align:center; margin:0 auto;z-index:1}
        .slider .bd,.slider .bd li,.slider .bd img{width:400px; height:272px}/*请给每个幻灯片套个div并设置id，通过id重置这个地方的宽度，达到自定义效果*/
        .slider .bd{z-index:2;overflow:hidden}
        .slider .bd li{float:left;width: 100%;overflow:hidden; background-position:center; background-repeat:no-repeat}
        .slider .bd li a{ display:block; width: 100%; height: 100%}
        .slider .bd li img{display:block}
        .slider .hd{ position: absolute; z-index: 3; left: 0; right: 0; bottom:10px; padding: 0 10px; text-align: center}
        .slider .hd li{display:inline-block;text-align:center;margin-right:10px;cursor:pointer;background-color:#C2C2C2}
        .slider .hd li.active{background-color:#222}
        /*圆点*/
        .dots li{width:10px; height:10px;font-size:0px;line-height:0px;border-radius:50%}
        /*数字*/
        .numbox li{width:20px; height:20px; line-height:20px; font-size:13px;font-family:Arial;font-weight:bold; text-indent:inherit}
        .numbox li.active{color:#fff}
        /*长方条*/
        .rectangle li{width:40px; height:10px;font-size:0px;line-height:0px}
    </style>
@endsection
@section('content')
<article class="cl pd-20">
	<form method="post" action="/admin/smember/update" class="form form-horizontal" id="form-smember-edit">
		<input type="hidden" value="{{$member->id}}" placeholder="" id="id" name="id">
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>店铺名：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="{{$member->store_name}}" placeholder="" required id="store_name" name="store_name">
			</div>
		</div>
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>地址：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="{{$member->address}}" required placeholder="" id="address" name="address">
			</div>
		</div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>店铺图片：</label>
            <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" value="{{$member->store_photos}}" readonly placeholder="" name="store_photos" id="store_photos">
            </div>
        </div>
        <div class="clearfix"></div>
        <?php $pics = explode(',', $member->store_photos);?>
        <div id="slider-3">
            <div class="slider">
                <div class="bd">
                    <ul>
                        <li><a href="#" target="_blank">
                                <img src="@if(isset($pics[0]) && $pics[0]){{$pics[0]}}@endif" alt="店铺图片1" class="img-thumbnail">
                            </a>
                        </li>
                        <li>
                            <a href="#" target="_blank">
                                <img src="@if(isset($pics[1]) && $pics[1]){{$pics[1]}}@endif" alt="店铺图片2" class="img-thumbnail">
                            </a>
                        </li>
                        <li>
                            <a href="#" target="_blank">
                                <img src="@if(isset($pics[2]) && $pics[2]){{$pics[2]}}@endif" alt="店铺图片3" class="img-thumbnail">
                            </a>
                        </li>
                    </ul>
                </div>
                <ol class="hd cl dots">
                    <li>1</li>
                    <li>2</li>
                    <li>3</li>
                    <li>4</li>
                </ol>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row cl">
            {{--<label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>店铺简介：</label>--}}
            <div class="formControls col-xs-8 col-sm-9 col-sm-offset-1">
                <div id="editormd">
                    <textarea name="store_introduction" style="display:none;">{{$member->store_introduction}}</textarea>
                </div>
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
<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/jquery.validate.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/validate-methods.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/messages_zh.js"></script>
<script type="text/javascript" src="/lib/jquery.SuperSlide/2.1.1/jquery.SuperSlide.min.js"></script>
<script type="text/javascript" src="/lib/editor.md/src/editormd.js"></script>
@endsection
<script type="text/javascript">
$(function(){
    var editor = editormd("editormd", {
        path : "/lib/editor.md/lib/", // Autoload modules mode, codemirror, marked... dependents libs path
        width:'800px',
        height:'399px',
        syncScrolling : "single",
        saveHTMLToTextarea : true,
        searchReplace: true,
        //watch : false,                // 关闭实时预览
        htmlDecode: "style,script,iframe|on*",            // 开启 HTML 标签解析，为了安全性，默认不开启
        //toolbar  : false,             //关闭工具栏
        //previewCodeHighlight : false, // 关闭预览 HTML 的代码块高亮，默认开启
        //emoji: true,
        taskList: true,
        tocm: true,         // Using [TOCM]
        tex: true,                   // 开启科学公式TeX语言支持，默认关闭
        flowChart: true,             // 开启流程图支持，默认关闭
        sequenceDiagram: true,       // 开启时序/序列图支持，默认关闭,
        //dialogLockScreen : false,   // 设置弹出层对话框不锁屏，全局通用，默认为true
        //dialogShowMask : false,     // 设置弹出层对话框显示透明遮罩层，全局通用，默认为true
        //dialogDraggable : false,    // 设置弹出层对话框不可拖动，全局通用，默认为true
        //dialogMaskOpacity : 0.4,    // 设置透明遮罩层的透明度，全局通用，默认值为0.1
        //dialogMaskBgColor : "#000", // 设置透明遮罩层的背景颜色，全局通用，默认为#fff
        //启动本地图片上传功能
        imageUpload:true,
        imageFormats   : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
        imageUploadURL : "{{route('service.raw.upload')}}"
    });
    jQuery("#slider-3 .slider").slide(
        {
            mainCell:".bd ul",
            titCell:".hd li",
            trigger:"click",
            effect:"leftLoop",
            autoPlay:true,
            delayTime:500,
            interTime:2500,
            pnLoop:false,
            titOnClassName:"active"
        });
	$("#form-smember-edit").validate({
		rules:{
            store_name:{
                required:true
			},
            address:{
                required:true
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