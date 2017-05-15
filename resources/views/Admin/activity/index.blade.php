@extends('Admin.layouts.common')

@section('content')
	<section class="Hui-article-box">
		<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 活动管理 <span class="c-gray en">&gt;</span> 每日专题 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
		<div class="Hui-article">
			<div >
				<div class="pd-20">
					<form class="text-c" method="get" action="{{route('admin.activity.index')}}"> 日期范围：
						<input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})" id="logmin" class="input-text Wdate" style="width:120px;" name="starttime" value="{{$request->input('starttime')}}">
						-
						<input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})" id="logmax" class="input-text Wdate" style="width:120px;" name="endtime" value="{{$request->input('endtime')}}">
						<input type="text" name="title" id="" placeholder=" 专题名称" style="width:250px" class="input-text" value="{{$request->input('title')}}">
						<button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 搜专题</button>
					</form>
					<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"><a href="javascript:;" onclick="datadel()" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a> <a class="btn btn-primary radius" onclick="product_add('添加专题','{{route('admin.activity.post')}}')" href="javascript:;"><i class="Hui-iconfont">&#xe600;</i> 添加专题 </a></span> <span class="r">共有数据：<strong>{{$actives->total()}}</strong> 条</span> </div>
					<div class="mt-20">
						<table class="table table-border table-bordered table-bg table-hover table-sort">
							<thead>
							<tr class="text-c">
								<th width="40"><input name="" type="checkbox" value=""></th>
								<th width="40">ID</th>
								<th width="60">主题名称</th>
								<th width="60">缩略图</th>
								<th >主题内容</th>
								<th width="60">上架状态</th>
								<th width="100">操作</th>
							</tr>
							</thead>
							<tbody>
							<form class="batchdelete" method="get">
								@foreach ($actives as $a)
									<tr class="text-c va-m">
										<td><input name="ids[]" type="checkbox" value="{{$a->id}}"></td>
										<td>{{$a->id}}</td>
										<td>{{$a->name}}</td>
										<td><a onClick="product_edit('专题编辑','{{route('admin.activity.post',array('id'=>$a->id))}}')" href="javascript:;"><img width="60" class="product-thumb" src="{{$a->photos}}"></a></td>

										<td class="text-l">
											@foreach($a->description as $vo)
												<img src="{{$vo}}" alt="" width="60" height="80" onClick="img_show('{{$vo}}')">
											@endforeach
										</td>
										<td class="td-status">@if($a->isable)<span class="label label-success radius">已上架</span>@else<span class="label label-default radius">已下架</span>@endif</td>
										<td class="td-manage">
											@if($a->isable)
												<a style="text-decoration:none" onClick="product_stop(this,'{{$a->id}}')" href="javascript:;" title="下架"><i class="Hui-iconfont">&#xe6de;</i></a>
											@else
												<a style="text-decoration:none" onClick="product_start(this,'{{$a->id}}')" href="javascript:;" title="上架"><i class="Hui-iconfont">&#xe6dc;</i></a>
											@endif
											<a style="text-decoration:none" class="ml-5" onClick="product_edit('产品编辑','{{route('admin.activity.post',array('id'=>$a->id))}}')" href="javascript:;" title="编辑"><i class="Hui-iconfont">&#xe6df;</i></a>
											<a style="text-decoration:none" class="ml-5" onClick="product_del(this,'{{$a->id}}')" href="javascript:;" title="删除"><i class="Hui-iconfont">&#xe6e2;</i></a>
										</td>
									</tr>
								@endforeach
							</form>
							</tbody>
						</table>
						{!! $actives->render() !!}
					</div>
				</div>
			</div>

		</div>
	</section>


	<!-- <script type="text/javascript" charset="utf-8">
        $("select[name='pcategory']").change(function(){
            var url = "/admin/category/getchilds/" + $(this).val();
            $.get(url,function(result){
                console.log(result);
            })
        })
    </script> -->
	<!--请在下方写此页面业务相关的脚本-->
	<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
	<script type="text/javascript" src="/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>
	<script type="text/javascript" src="/lib/zTree/v3/js/jquery.ztree.all-3.5.min.js"></script>
	<script type="text/javascript">
		/* 图片展示*/
		function img_show(url){
			layer_show('图片展示',url);
		}
		/*专题-编辑*/
		function product_edit(title,url){
			var index = layer.open({
				type: 2,
				title: title,
				content: url
			});
			layer.full(index);
		}
		function product_add(title,url){
			var index = layer.open({
				type: 2,
				title: title,
				content: url
			});
			layer.full(index);
		}

		/*专题-下架*/
		function product_stop(obj,id){
			layer.confirm('确认要下架吗？',function(index){
				$.get('/admin/activity/changestatus',{'id':id},function(result){
					if(result.errorCode==0){
						$(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="product_start(this,'+id+')" href="javascript:;" title="上架"><i class="Hui-iconfont">&#xe6dc;</i></a>');
						$(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius">已下架</span>');
						$(obj).remove();
						layer.msg('已下架!',{icon: 5,time:1000});
					}else{
						layer.msg('系统出错!',{icon: 0,time:1000});
					}
				},'json');

			});
		}

		/*专题-发布*/
		function product_start(obj,id){
			layer.confirm('确认要上架吗？',function(index){
				$.get('/admin/activity/changestatus',{'id':id},function(result){
					if(result.errorCode==0){
						$(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="product_stop(this,'+id+')" href="javascript:;" title="下架"><i class="Hui-iconfont">&#xe6de;</i></a>');
						$(obj).parents("tr").find(".td-status").html('<span class="label label-success radius">已上架</span>');
						$(obj).remove();
						layer.msg('已上架!',{icon: 6,time:1000});
					}else{
						layer.msg('系统出错!',{icon: 0,time:1000});
					}
				},'json');

			});
		}

		/*专题-删除*/
		function product_del(obj,id){
			layer.confirm('确认要删除吗？',function(index){
				$.get('/admin/activity/delete',{'id':id},function(result){
					if(result.errorCode==0){
						$(obj).parents("tr").remove();
						layer.msg('已删除!',{icon:1,time:1000});
					}else{
						layer.msg('删除出错!',{icon:0,time:1000});
					}
				},'json');

			});
		}
		/*专题-批量删除*/
		function datadel(){
			var data = $('form').serialize();
			layer.confirm('确认要删除吗？',function(index){
				$.get('/admin/activity/delete',data,function(result){
					if(result.errorCode==0){
						// $(obj).parents("tr").remove();
						layer.msg('已删除!',{icon:1,time:1000});
						setTimeout(function(){window.location.reload()},1000);
					}else{
						layer.msg('删除出错!',{icon:0,time:1000});
					}
				},'json');

			});
		}
	</script>
@endsection

