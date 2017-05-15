@extends('Admin.layouts.common')

@section('content')
<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 代理人管理 <span class="c-gray en">&gt;</span> 代理人列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<div >
			<div class="pd-20">
				<form class="text-c" method="get" action="{{route('admin.agent.index')}}"> 日期范围：
					<input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})" id="logmin" class="input-text Wdate" style="width:120px;" name="starttime" value="{{$request->input('starttime')}}">
					-
					<input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})" id="logmax" class="input-text Wdate" style="width:120px;" name="endtime" value="{{$request->input('endtime')}}">
					<input type="text" name="name" id="" placeholder=" 代理人姓名/手机号" style="width:250px" class="input-text" value="{{$request->input('name')}}">
					<button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 搜代理</button>
				</form>
				<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"> <a class="btn btn-primary radius" onclick="agent_add('添加代理人','{{route('admin.agent.post')}}')" href="javascript:;"><i class="Hui-iconfont">&#xe600;</i> 添加代理人</a></span> <span class="r">共有数据：<strong>{{$agents->total()}}</strong> 条</span> </div>
				<div class="mt-20">
					<table class="table table-border table-bordered table-bg table-hover table-sort">
						<thead>
							<tr class="text-c">
								<th width="40">ID</th>
								<th width="60">姓名</th>
								<th width="100">手机号</th>
								<th width="150">地址</th>
								<th width="150">现金</th>
								<th>备注</th>
								<th >操作</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($agents as $a)
							<tr class="text-c va-m">
								<td>{{$a->id}}</td>
								<td><a onClick="agent_edit('代理人编辑','{{route('admin.agent.post',array('id'=>$a->id))}}')" href="javascript:;">{{$a->agentname}}</a></td>
								
								<td class="text-l">{{$a->phone}}</td>
								<td>{{$a->agentaddr}}</td>
								<td><a onClick="agent_logs('资金流水','{{route('admin.agent.logs',array('id'=>$a->id))}}')" href="javascript:;">{{$a->cash_total}}</a></td>
								<td>{{$a->note}}</td>
								<td class="td-manage">
									<a style="text-decoration:none" onClick="settle('结算','{{route('admin.agent.settle', array('agent_id'=>$a->id))}}')" href="javascript:;" title="结算"><i class="Hui-iconfont">&#xe6e1;</i></a>
									<a style="text-decoration:none" class="ml-5" onClick="agent_edit('信息编辑','{{route('admin.agent.post',array('id'=>$a->id))}}')" href="javascript:;" title="编辑"><i class="Hui-iconfont">&#xe6df;</i></a>
									<a style="text-decoration:none" onClick="del(this,'/admin/agentdelete',{{$a->id}})" href="javascript:;" title="删除"><i class="Hui-iconfont">&#xe6e2;</i></a>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					{!! $agents->render() !!}
				</div>
			</div>
		</div>

	</div>
</section>
<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>
<script type="text/javascript" src="/lib/zTree/v3/js/jquery.ztree.all-3.5.min.js"></script>
<script type="text/javascript">
/*代理人-编辑*/
function agent_edit(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url,
		area: ['500px', '550px']
	});
	// layer.full(index);
}
function agent_add(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url,
		area: ['500px', '550px']
	});
	// layer.full(index);
}
function agent_logs(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url,
		area: ['800px', '650px']
	});
}
function settle(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url,
		area: ['520px', '300px']
	});
	// layer.full(index);
}
/*代理人-删除*/
function del(obj,url,id){
	layer.confirm('确认要删除吗？',function(index){
		$.get(url,{'id':id},function(result){
			if(result.errorCode==0){
				$(obj).parents("tr").remove();
				layer.msg('已删除!',{icon:1,time:1000});
			}else{
				layer.msg('删除出错!',{icon:0,time:1000});
			}
		},'json');
		
	});
}
</script>
@endsection

