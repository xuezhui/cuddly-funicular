@extends('Admin.layouts.common')

@section('content')

<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 幻灯片管理 <span class="c-gray en">&gt;</span> 幻灯片管理 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"><a href="javascript:;" onclick="datadel()" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a> <a class="btn btn-primary radius" onclick="filmslide_add('添加幻灯片','{{route('admin.filmslide.post')}}')" href="javascript:;"><i class="Hui-iconfont">&#xe600;</i> 添加幻灯片</a></span><span class="r">共有数据：<strong>{{$slides->total()}}</strong> 条</span> </div>
			<div class="mt-10">
				<table class="table table-border table-bordered table-bg table-sort">
					<thead>
						<tr class="text-c">
							<th ><input type="checkbox" name="" value=""></th>
							<th >ID</th>
							<th >排序</th>
							<th >别名</th>
							<th >幻灯片</th>
							<th >链接</th>
							<th>发布状态</th>
							<th >操作</th>
						</tr>
					</thead>
					<tbody>
						<form method="post" action="/admin/filmslide/index" method="post">
						@foreach ($slides as $s)
							<tr class="text-c ">
								<td><input name="ids[]" type="checkbox" value="{{$s->id}}"></td>
								<td>{{$s->id}}</td>
								<td><input name="displayorder[{{$s->id}}]" type="number" value="{{$s->displayorder}}" class="input-text"></td>
								<td>{{$s->title}} </td>
								<td class="text-l"><img src="{{$s->photos}}" style="width:50px;height:50px;"> </td>
								<td>{{$s->link}} </td>
								<td class="td-status">@if($s->isable)<span class="label label-success radius">已启用</span>@else<span class="label label-default radius">未启用</span>@endif</td>
						    	<td class="f-14 td-manage">
						    		@if($s->isable)
									<a style="text-decoration:none" onClick="filmslide_stop(this,'{{$s->id}}')" href="javascript:;" title="收回" class="btn btn-default"><i class="Hui-iconfont">&#xe6de;</i></a> 
									@else
									<a style="text-decoration:none" onClick="filmslide_start(this,'{{$s->id}}')" href="javascript:;" title="发布" class="btn btn-default"><i class="Hui-iconfont">&#xe6dc;</i></a>
									@endif
						    		<a style="text-decoration:none" onClick="filmslide_add('编辑','{{route('admin.filmslide.post',array('id'=>$s->id))}}','1')" href="javascript:;" title="编辑" class="btn btn-default"><i class="Hui-iconfont">&#xe6df;编辑</i></a> 
						    		<a style="text-decoration:none" class="ml-5 btn btn-default" onClick="active_del(this,{{$s->id}})" href="javascript:;" title="删除"><i class="Hui-iconfont" >&#xe6e2;删除</i></a>
						    	</td>
						    </tr>
						    
						@endforeach
						</form>
					</tbody>
				</table>
				<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"><a href="javascript:;" class="btn btn-warning radius displayorder"><i class="Hui-iconfont">&#xe603;</i> 提交排序</a></span></div>
				{{$slides->render()}}
			</div>
		</article>
	</div>
</section>
<script  type="text/javascript" charset="utf-8">
	/*幻灯片-添加/编辑*/
	function filmslide_add(title,url){
		var index = layer.open({
			type: 2,
			title: title,
			content: url,
			area: ['520px', '570px']
		});
		// layer.full(index);
	}
	/*幻灯片-删除*/
	function active_del(obj,id){
		layer.confirm('确认要删除吗？',function(index){
			
			$.post('{{route('admin.filmslide.delete')}}',{'id':id},function(result){
				if(result.errorCode==0){
					$(obj).parents("tr").remove();
					layer.msg('已删除!',{icon:1,time:1000});
				}else{
					layer.msg('出错!',{icon:0,time:1000});
				}
			},'json')
			
		});
	}
	/*幻灯片-批量删除*/
	function datadel(){
		layer.confirm('确认要删除吗？',function(index){
			var data = $('form').serialize();
			$.post('{{route('admin.filmslide.delete')}}',data,function(result){
				result = JSON.parse(result);
				if(result.errorCode==0){
					// $(obj).parents("tr").remove();
					layer.msg('已删除!',{icon:1,time:1000});
					setTimeout(function(){window.location.reload()},1000);
				}else{
					layer.msg('出错!',{icon:0,time:1000});
				}
			})
			
		});
	}
	/*幻灯片-收回*/
	function filmslide_stop(obj,id){
		layer.confirm('确认要收回吗？',function(index){
			$.get('/admin/filmslide/changestatus',{'id':id},function(result){
				if(result.errorCode==0){
					$(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="filmslide_start(this,'+id+')" href="javascript:;" title="发布" class="btn btn-default"><i class="Hui-iconfont">&#xe6dc;</i></a>');
					$(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius">未启用</span>');
					$(obj).remove();
					layer.msg('已收回!',{icon: 5,time:1000});
				}else{
					layer.msg('系统出错!',{icon: 0,time:1000});
				}
			},'json');
			
		});
	}

	/*幻灯片-发布*/
	function filmslide_start(obj,id){
		layer.confirm('确认要发布吗？',function(index){
			$.get('/admin/filmslide/changestatus',{'id':id},function(result){
				if(result.errorCode==0){
					$(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="filmslide_stop(this,'+id+')" href="javascript:;" title="收回" class="btn btn-default"><i class="Hui-iconfont">&#xe6de;</i></a>');
					$(obj).parents("tr").find(".td-status").html('<span class="label label-success radius">已启用</span>');
					$(obj).remove();
					layer.msg('已启用!',{icon: 6,time:1000});
				}else{
					layer.msg('系统出错!',{icon: 0,time:1000});
				}
			},'json');
			
		});
	}
	/*幻灯片-修改排序*/
	$('.displayorder').click(function(){
		$('form').submit();
	})
</script>

@endsection
