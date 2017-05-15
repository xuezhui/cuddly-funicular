@extends('Admin.layouts.common')

@section('content')
<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 管理中心 <span class="c-gray en">&gt;</span> 会员列表<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<form class="text-c" action="/admin/agentmember/index" method="get"> 日期范围：
				<input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})" id="datemin" class="input-text Wdate" style="width:120px;" value="{{$request->input('starttime')}}" name="starttime">
				-
				<input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d'})" id="datemax" class="input-text Wdate" style="width:120px;" value="{{$request->input('endtime')}}" name="endtime">
				<input type="text" class="input-text" style="width:250px" placeholder="输入会员名称、电话、邮箱" id="" name="member" value="{{$request->input('member')}}">
				<button type="submit" class="btn btn-success radius" id="" name=""><i class="Hui-iconfont">&#xe665;</i> 搜用户</button>
			</form>
			<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"> <a href="javascript:;" onclick="member_add('添加会员','{{route('admin.agentmember.post')}}')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加用户</a></span><span class="r">共有数据：<strong>{{$members->total()}}</strong> 条</span> </div>
			<div class="mt-20">
				<table class="table table-border table-bordered table-hover table-bg table-sort">
					<thead>
						<tr class="text-c">
							<th width="25"><input type="checkbox" name="" value=""></th>
							<th width="80">编号</th>
							<th width="100">昵称</th>
							<th width="90">手机</th>
							<th width="150">登录IP</th>
							<th width="130">加入时间</th>
							<th width="70">状态</th>
						</tr>
					</thead>
					<tbody>
					<form method="post" class="datadel" action="{{route('admin.member.delete')}}" id="form">
					@foreach($members as $member)
						<tr class="text-c">
							<td><input type="checkbox" value="{{$member->id}}" name="ids[]"></td>
							<td>{{$member->id}}</td>
							<td><u style="cursor:pointer" class="text-primary" onclick="member_edit('{{$member->nickname}}','{{route('admin.member.show', array('id'=>$member->id))}}','10001','360','400')">{{$member->nickname}}</u></td>
							<td>{{$member->telephone}}</td>
							<td>{{long2ip($member->last_login_IP)}}</td>
							<td>{{$member->created_at}}</td>
							<td class="td-status"><span class="label label-success radius">@if($member->status == 1)正常@elseif($member->status == 2)草稿@elseif($member->status == 3)待审@else未知@endif</span></td>
						</tr>
					@endforeach
					</form>
					</tbody>
				</table>
				{{$members->render()}}
			</div>
		</article>
	</div>
</section>

<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>

<script type="text/javascript">
/*用戶-编辑*/
function member_edit(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url,
		area: ['500px', '550px']
	});
	// layer.full(index);
}
function member_add(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url,
		area: ['500px', '550px']
	});
	// layer.full(index);
}
/*會員-删除*/
function member_del(obj,id){
	layer.confirm('确认要删除吗？',function(index){
		$.post('/admin/agentmember/delete',{'id':id},function(result){
			if(result.errorCode==0){
				$(obj).parents("tr").remove();
				layer.msg('已删除!',{icon:1,time:1000});
			}else{
				layer.msg('删除出错!',{icon:0,time:1000});
			}
		},'json');
		
	});
}
function datadel(){
	layer.confirm('确认要删除吗？',function(index){
		var data = $('form').serialize();
		$.post('/admin/agentmember/delete',data,function(result){
			if(result.errorCode==0){
				layer.msg('已删除!',{icon:1,time:1000});
				window.location.reload();
			}else{
				layer.msg('删除出错!',{icon:0,time:1000});
			}
		},'json');
		
	});
}
</script>
@endsection