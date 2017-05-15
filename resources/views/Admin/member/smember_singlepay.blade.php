@extends('Admin.layouts.pop')
@section('my-css')
<style>
    .Hui-iconfont {
        font-size: 1.3rem;
        color: #21c371;
    }
	.td-manage>a {
		padding-right:3%;
	}
</style>
@endsection
@section('content')
<section class="">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页
        <span class="c-gray en">&gt;</span> 服务会员管理 <span class="c-gray en">&gt;</span> 用户支付列表
    </nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<form class="text-c" action="/admin/smember/singlepay" method="post"> 日期范围：
				<input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})" id="datemin" class="input-text Wdate" style="width:120px;" value="{{$request->input('starttime')}}" name="starttime">
				-
				<input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d'})" id="datemax" class="input-text Wdate" style="width:120px;" value="{{$request->input('endtime')}}" name="endtime">
				<input type="text" class="input-text" style="width:250px" placeholder="输入关键词" id="" name="keywords" value="{{$request->input('keywords')}}">
                <input type="hidden" name="id" value="{{$request->input('id')}}">
				<button type="submit" class="btn btn-success radius" id="" name=""><i class="Hui-iconfont">&#xe665;</i> 搜用户</button>
			</form>
			<div class="cl pd-5 bg-1 bk-gray mt-20">
				<span class="l">
				</span>
				<span class="r">共有数据：<strong>{{$logs->total()}}</strong> 条</span>
			</div>
			<div class="mt-20">
				<table class="table table-border table-bordered table-hover table-bg table-sort">
					<thead>
						<tr class="text-c">
							{{--<th width="25"><input type="checkbox" name="" value=""></th>--}}
							<th width="20">编号</th>
							<th width="80">消费者</th>
							<th width="80">消费者手机</th>
							<th width="80">金额</th>
							<th width="80">备注</th>
							<th width="80">创建时间</th>
							<th width="80">修改时间</th>
							{{--<th width="100">操作</th>--}}
						</tr>
					</thead>
					<tbody>
					<form method="post" class="datadel" action="{{route('admin.smember.batch_delete')}}">
                        {{ method_field('DELETE') }}
					@foreach($logs as $log)
						<tr class="text-c">
							{{--<td><input type="checkbox" value="{{$log->id}}" name="ids[]"></td>--}}
							<td>{{$log->id}}</td>
							<td>{{$log->user->nickname}}</td>
							<td>{{$log->user->telephone}}</td>
							<td>{{$log->amount}}</td>
							<td>{{$log->note}}</td>
							<td>{{$log->created_at}}</td>
							<td>{{$log->updated_at}}</td>
							{{--<td class="td-manage">
                                <a title="编辑" href="javascript:;" onclick="member_edit('编辑','{{route('admin.smember.edit', array('id'=>$log->id))}}','','650')" class="ml-5" style="text-decoration:none">
                                    <i class="Hui-iconfont">&#xe6df;</i></a>
                                <a title="删除" href="javascript:;" onclick="member_del(this,{{$log->id}})" class="ml-5" style="text-decoration:none">
                                    <i class="Hui-iconfont">&#xe6e2;</i>
                                </a>
                            </td>--}}
						</tr>
					@endforeach
					</form>
					</tbody>
				</table>
				{{$logs->render()}}
			</div>
		</article>
	</div>
</section>
@endsection
<!--请在下方写此页面业务相关的脚本-->
@section('my-js')
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>
@endsection
<script type="text/javascript">
/*用户-查看*/
function member_show(title,url,w,h){
	layer_show(title,url,w,h);
}
/*用户-编辑*/
function member_edit(title,url,w,h){
	layer_show(title,url,w,h);
}
//批量删除用户
function batch_del(){
    layer.confirm('确认要删除吗？',function(index){
        var data = $('.datadel').serialize();
        $.post('{{route('admin.smember.batch_delete')}}',data,function(result){
            result = JSON.parse(result);
            if(result.errorCode==0){
                layer.msg('已删除!',{icon:1,time:1000},function(){window.location.reload();});
            } else {
                layer.msg('出错!',{icon:0,time:1000});
            }
        })

    });
}

/*用户-删除*/
function member_del(obj,id){
    layer.confirm('确认要删除吗？',function(index){
        $.post('{{route('admin.smember.delete')}}',{'id':id,'_method':'delete'},function(result){
            result = JSON.parse(result);
            if(result.errorCode==0){
                $(obj).parents("tr").remove();
                layer.msg('已删除!',{icon:1,time:1000});
            }else{
                layer.msg('出错!',{icon:0,time:1000});
            }
        });
    });
}
</script>