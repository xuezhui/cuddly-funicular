@extends('Admin.layouts.common')

@section('content')

<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 产品管理 <span class="c-gray en">&gt;</span> 产品分类 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"><a href="javascript:;" onclick="datadel()" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a> <a class="btn btn-primary radius" onclick="category_add('添加类目','{{route('admin.category.post')}}')" href="javascript:;"><i class="Hui-iconfont">&#xe600;</i> 添加类目</a></span><span class="r">共有数据：<strong>{{$categoryList->total()}}</strong> 条</span> </div>
			<div class="mt-10">
				<table class="table table-border table-bordered table-bg table-sort">
					<thead>
						<tr class="text-c">
							<th ><input type="checkbox" name="" value=""></th>
							<th >ID</th>
							<th >类目 <span class="label label-warning">点击显示子类<i class="icon Hui-iconfont">&#xe6d5;</i></span></th>
							<th >操作</th>
						</tr>
					</thead>
					<tbody>
						<form method="post" action="{{route('admin.category.delete')}}">
						@foreach ($categoryList as $cate)
							<tr class="text-c ">
								<td><input name="ids[]" type="checkbox" value="{{$cate->id}}"></td>
								<td>{{$cate->id}}</td>
								<td class="text-l level1"><img src="{{$cate->photos}}" style="width:50px;height:50px;"> {{$cate->category_name}} </td>
						    	<td class="f-14 product-brand-manage">
						    		<a style="text-decoration:none" onClick="category_add('添加子类','{{route('admin.category.post',array('pid'=>$cate->id))}}','1')" href="javascript:;" title="添加子类" class="btn btn-default"><i class="Hui-iconfont">&#xe600;添加子类</i></a>
						    		<a style="text-decoration:none" onClick="category_add('编辑','{{route('admin.category.post',array('id'=>$cate->id))}}','1')" href="javascript:;" title="编辑" class="btn btn-default"><i class="Hui-iconfont">&#xe6df;编辑</i></a> 
						    		<a style="text-decoration:none" class="ml-5 btn btn-default" onClick="active_del(this,{{$cate->id}})" href="javascript:;" title="删除"><i class="Hui-iconfont" >&#xe6e2;删除</i></a>
						    	</td>
						    </tr>
						    <tr style="display:none;">
						    	<td colspan="4">
						    		<table >
								    	<tr>
								    		<td colspan="4">{{$cate->category_name}}--子类</td>
								    	</tr>
						    			@foreach($cate->childs as $c)
						    			<tr class="text-c">
								    		<td><input name="ids[]" type="checkbox" value="{{$c->id}}"></td>
											<td>{{$c->id}}</td>
											<td class="text-l"><img src="{{$c->photos}}" style="width:50px;height:50px;"> {{$c->category_name}}</td>
									    	<td class="f-14 product-brand-manage">
									    		<a style="text-decoration:none" onClick="category_add('编辑','{{route('admin.category.post',array('id'=>$c->id))}}','1')" href="javascript:;" title="编辑" class="btn btn-default"><i class="Hui-iconfont">&#xe6df;编辑</i></a> 
									    		<a style="text-decoration:none" class="ml-5 btn btn-default" onClick="active_del(this,{{$c->id}})" href="javascript:;" title="删除"><i class="Hui-iconfont" >&#xe6e2;删除</i></a>
									    	</td>
							    		</tr>
						    			@endforeach
						    		</table>
						    	</td>
						    </tr>
						    
						@endforeach
						</form>
					</tbody>
				</table>
				{{$categoryList->render()}}
			</div>
		</article>
	</div>
</section>
<script  type="text/javascript" charset="utf-8">
	/*类目-添加/编辑*/
	function category_add(title,url){
		var index = layer.open({
			type: 2,
			title: title,
			content: url,
			area: ['480px', '460px']
		});
		// layer.full(index);
	}
	/*类目-删除*/
	function active_del(obj,id){
		layer.confirm('确认要删除吗？',function(index){
			
			$.post('{{route('admin.category.delete')}}',{'id':id},function(result){
				result = JSON.parse(result);
				if(result.errorCode==0){
					$(obj).parents("tr").remove();
					layer.msg('已删除!',{icon:1,time:1000});
				}else{
					layer.msg('出错!',{icon:0,time:1000});
				}
			})
			
		});
	}
	/*类目-批量删除*/
	function datadel(){
		layer.confirm('确认要删除吗？',function(index){
			var data = $('form').serialize();
			$.post('{{route('admin.category.delete')}}',data,function(result){
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
	$('.level1').click(function(){
		$(this).parents('tr').next().toggle();
	})
</script>

@endsection
