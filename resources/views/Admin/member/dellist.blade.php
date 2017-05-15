@extends('Admin.layouts.common')

@section('content')
<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 用户中心 <span class="c-gray en">&gt;</span> 删除的用户<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<form class="text-c" action="/admin/member/dellist" method="get"> 日期范围：
				<input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})" id="datemin" class="input-text Wdate" style="width:120px;" value="{{$request->input('starttime')}}" name="starttime">
				-
				<input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d'})" id="datemax" class="input-text Wdate" style="width:120px;" value="{{$request->input('endtime')}}" name="endtime">
				<input type="text" class="input-text" style="width:250px" placeholder="输入会员名称、电话、邮箱" id="" name="member" value="{{$request->input('member')}}">
				<button type="submit" class="btn btn-success radius" id="" name=""><i class="Hui-iconfont">&#xe665;</i> 搜用户</button>
			</form>
			<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"><a href="javascript:;" onclick="data_restore()" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量复原</a> </span> <span class="r">共有数据：<strong>{{$count}}</strong> 条</span> </div>
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
						<th width="100">操作</th>
					</tr>
				</thead>
				<tbody>
				<form method="post" class="datarestore" action="{{route('admin.member.restore')}}">
					@foreach($members as $member)
					<tr class="text-c">
						<td><input type="checkbox" value="{{$member->id}}" name="ids[]"></td>
						<td>{{$member->id}}</td>
						<td><u style="cursor:pointer" class="text-primary" onclick="member_show('{{$member->nickname}}','{{route('admin.member.show', array('id'=>$member->id))}}','10001','360','400')">{{$member->nickname}}</u></td>
						<td>{{$member->telephone}}</td>
						<td>{{$member->last_login_IP}}</td>
						<td>{{$member->created_at}}</td>
						<td class="td-status"><span class="label label-danger radius">删除</span></td>
						<td class="td-manage"><a style="text-decoration:none" href="javascript:;" onClick="member_restore(this,{{$member->id}})" title="还原"><i class="Hui-iconfont">&#xe66b;</i></a> <!--<a title="删除" href="javascript:;" onclick="member_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>--></td>
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
<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>

<script type="text/javascript">
/*用户-查看*/
function member_show(title,url,id,w,h){
	layer_show(title,url,w,h);
}
/*用户-还原*/
function member_restore(obj,id){
	layer.confirm('确认要还原吗？',function(index){

        $.post('{{route('admin.member.restore')}}',{'id':id},function(result){
            result = JSON.parse(result);
            if(result.errorCode==0){
                $(obj).parents("tr").remove();
                layer.msg('已还原!',{icon: 6,time:1000});
            }else{
                layer.msg('出错!',{icon:0,time:1000});
            }
        })
	});
}

//批量还原用户
function data_restore(){
    layer.confirm('确认要还原吗？',function(index){
        var data = $('.datarestore').serialize();
        $.post('{{route('admin.member.restore')}}',data,function(result){
            result = JSON.parse(result);
            if(result.errorCode==0){
                // $(obj).parents("tr").remove();
                layer.msg('已还原!',{icon: 6,time:1000});
                setTimeout(function(){window.location.reload()},1000);
            }else{
                layer.msg('出错!',{icon:0,time:1000});
            }
        })

    });
}

/*用户-删除*/
function member_del(obj,id){
	layer.confirm('确认要删除吗？',function(index){
		$(obj).parents("tr").remove();
		layer.msg('已删除!',{icon:1,time:1000});
	});
}
</script>
@section('content')