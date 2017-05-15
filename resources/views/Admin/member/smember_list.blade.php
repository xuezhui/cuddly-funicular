@extends('Admin.layouts.common')
@section('my-css')
<style>
    .Hui-iconfont {
        font-size: 1.3rem;
        color: #21c371;
    }
    .td-manage>a {
        padding-right:3%;
    }
    img {
        width:120px;
        height:120px;
    }
    .inline {
        display: inline-block;
        float: none;
    }
</style>
@endsection
@section('content')
<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页
        <span class="c-gray en">&gt;</span> 服务会员管理 <span class="c-gray en">&gt;</span> 服务会员列表
        <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
            <i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<form class="text-c" action="/admin/smember" method="post">
                <div class="col-sm-1 inline">
                <span class="select-box">
                  <select class="select" size="1" name="apply_progress">
                    <option value="" selected>审核状态</option>
                      @foreach($apply_progress as $key => $item)
                        <option value="{{$key}}">{{$item}}</option>
                      @endforeach
                </select>
                </span>
                </div>
                日期范围：
				<input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})" id="datemin" class="input-text Wdate" style="width:120px;" value="{{$request->input('starttime')}}" name="starttime">
				-
				<input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d'})" id="datemax" class="input-text Wdate" style="width:120px;" value="{{$request->input('endtime')}}" name="endtime">
				<input type="text" class="input-text" style="width:250px" placeholder="输入会员名称、店铺名称、电话" id="" name="keywords" value="{{$request->input('keywords')}}">
				<button type="submit" class="btn btn-success radius" id="" name=""><i class="Hui-iconfont">&#xe665;</i> 搜用户</button>
			</form>
			<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
					<a href="javascript:;" onclick="batch_del()" class="btn btn-danger radius">
						<i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a>
				</span><span class="r">共有数据：<strong>{{$members->total()}}</strong> 条</span>
            </div>
			<div class="mt-20">
				<table class="table table-border table-bordered table-hover table-bg table-sort">
					<thead>
						<tr class="text-c">
							<th width="25"><input type="checkbox" name="" value=""></th>
							<th width="20">编号</th>
							<th width="20">店铺头像1</th>
							<th width="20">店铺头像2</th>
							<th width="20">店铺头像3</th>
							<th width="50">店铺名</th>
							<th width="80">手机</th>
							<th width="90">地址</th>
							<th width="90">简介</th>
							<th width="50">登录IP</th>
							<th width="50">加入时间</th>
							<th width="20">审核状态</th>
							<th width="20">账号状态</th>
							<th width="100">操作</th>
						</tr>
					</thead>
					<tbody>
					<form method="post" class="datadel" action="{{route('admin.smember.batch_delete')}}">
                        {{ method_field('DELETE') }}
					@foreach($members as $member)
						<tr class="text-c">
							<td><input type="checkbox" value="{{$member->id}}" name="ids[]"></td>
							<td>{{$member->id}}</td>
							<td>
                                <?php
                                $pics = explode(',', $member->store_photos);
                                ?>
                                @if(isset($pics[0]))
                                    <img src="{{$pics[0]}}" alt="店铺头像1">
                                @endif
                            </td>
                            <td>
                                @if(isset($pics[1]))
                                    <img src="{{$pics[1]}}" alt="店铺头像2">
                                @endif
                            </td>
                            <td>
                                @if(isset($pics[2]))
                                    <img src="{{$pics[2]}}" alt="店铺头像3">
                                @endif
                            </td>
							<td>
                                <u style="cursor:pointer" class="text-primary" onclick="member_show('{{$member->store_name}}','{{route('admin.member.show', array('id'=>$member->id))}}','360','400')">
                                    {{$member->store_name}}
                                </u>
                            </td>
							<td>{{$member->telephone}}</td>
							<td>{{$member->address}}</td>
							<td>{{str_limit(strip_tags($member->store_introduction, 20))}}</td>
							<td>{{long2ip($member->last_login_IP)}}</td>
							<td>{{$member->created_at}}</td>
							<td class="td-status">
                                    @if($member->apply_progress == 0)
                                    <span class="label label-danger radius">冻结</span>
                                    @elseif($member->apply_progress == 1)
                                    <span class="label label-primary radius">待审核</span>
                                    @elseif($member->apply_progress == 2)
                                    <span class="label label-success radius">审核通过</span>
                                    @elseif($member->apply_progress == 3)
                                    <span class="label label-warning radius">审核不通过</span>
                                    @else未知
                                    @endif
                            </td>
                            <td class="td-status">
                                <span class="label label-success radius">
                                    @if($member->status == 1)正常
                                    @elseif($member->status == 2)草稿
                                    @elseif($member->status == 3)待审
                                    @else未知
                                    @endif
                                </span>
                            </td>
							<td class="td-manage">
                                <a style="text-decoration:none" onClick="member_audit('审核','{{route('admin.smember.audit', array('id'=>$member->id))}}','','300')" href="javascript:;" title="审核">
                                    <i class="Hui-iconfont">&#xe6e1;</i>
                                </a>
                                <a style="text-decoration:none" onClick="member_capital('账单','{{route('admin.smember.paylog', array('id'=>$member->id))}}','900','600')" href="javascript:;" title="账单">
                                    <i class="Hui-iconfont">&#xe628;</i>
                                </a>
                                <a title="编辑" href="javascript:;" onclick="member_edit('编辑','{{route('admin.smember.edit', array('id'=>$member->id))}}','960','650')" class="ml-5" style="text-decoration:none">
                                    <i class="Hui-iconfont">&#xe6df;</i></a>
                                <a title="删除" href="javascript:;" onclick="member_del(this,{{$member->id}})" class="ml-5" style="text-decoration:none">
                                    <i class="Hui-iconfont">&#xe6e2;</i>
                                </a>
                            </td>
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
/*用户-查看*/
function member_show(title,url,w,h){
	layer_show(title,url,w,h);
}
/*用户-审核*/
function member_audit(title,url,w,h){

	layer_show(title,url,w,h);
}
//用户账单
function member_capital(title,url,w,h) {
    layer_show(title,url,w,h);
}
/*用户-编辑*/
function member_edit(title,url,w,h){
	layer_show(title,url,w,h);
}
//批量删除用户
function batch_del(){
    layer.confirm('<span style="color: #c66161;">删除服务会员的同时它下面会员的支付列表也将丢失，确认要删除吗？</span>',function(index){
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
    layer.confirm('<span style="color: #c66161;">删除服务会员的同时它下面会员的支付列表也将丢失，确认要删除吗？</span>',function(index){

        $.post('{{route('admin.smember.delete')}}',{'id':id,'_method':'delete'},function(result){
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
</script>
@endsection