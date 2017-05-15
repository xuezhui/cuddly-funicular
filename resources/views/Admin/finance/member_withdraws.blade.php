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
        <span class="c-gray en">&gt;</span> 财务管理 <span class="c-gray en">&gt;</span> 会员提现列表
        <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
            <i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<form class="text-c" action="/admin/finance/member_withdraw" method="post">
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
				<button type="submit" class="btn btn-success radius" id="" name=""><i class="Hui-iconfont">&#xe665;</i> 搜用户</button>
			</form>
			<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
				</span><span class="r">共有数据：<strong>{{$withdraws->total()}}</strong> 条</span>
            </div>
			<div class="mt-20">
				<table class="table table-border table-bordered table-hover table-bg table-sort">
					<thead>
						<tr class="text-c">
							<th width="20">编号</th>
							<th width="50">用户名</th>
							<th width="80">手机号码</th>
							<th width="90">提现金额</th>
							<th width="90">卡号</th>
							<th width="90">所属银行</th>
							<th width="90">真实姓名</th>
							<th width="50">登录IP</th>
							<th width="50">申请时间</th>
							<th width="20">审核状态</th>
							<th width="20">账号状态</th>
							<th width="20">会员</th>
							<th width="100">操作</th>
						</tr>
					</thead>
					<tbody>
					@foreach($withdraws as $withdraw)
						<tr class="text-c">
							<td>{{$withdraw->id}}</td>
							<td>
                                <u style="cursor:pointer" class="text-primary" onclick="member_show('{{$withdraw->member_detail->nickname}}','{{route('admin.member.show', array('id'=>$withdraw->member_detail->id))}}','360','400')">
                                    {{$withdraw->member_detail->nickname or ''}}
                                </u>
                            </td>
							<td>{{$withdraw->member_detail->telephone or ''}}</td>
							<td>{{$withdraw->amount}}</td>
							<td>
                                {{$withdraw->card->card_number or ''}}
                            </td>
                            <td>
                                {{$withdraw->card->bank or ''}}
                            </td>
                            <td>
                                {{$withdraw->card->realname or ''}}
                            </td>
							<td>{{long2ip($withdraw->member_detail->last_login_IP or 0)}}</td>
							<td>{{$withdraw->created_at}}</td>
							<td class="td-status">
                                    @if($withdraw->apply_progress == 0)
                                    <span class="label label-danger radius">冻结</span>
                                    @elseif($withdraw->apply_progress == 1)
                                    <span class="label label-primary radius">待审核</span>
                                    @elseif($withdraw->apply_progress == 2)
                                    <span class="label label-success radius">审核通过</span>
                                    @elseif($withdraw->apply_progress == 3)
                                    <span class="label label-warning radius">审核不通过</span>
                                    @else未知
                                    @endif
                            </td>
                            <td class="td-status">
                                @if($withdraw->status == 1)
                                <span class="label label-success radius">正常</span>
                                @elseif($withdraw->status == 2)
                                    <span class="label label-primary radius">草稿</span>
                                @elseif($withdraw->status == 3)
                                    <span class="label label-primary radius">待审</span>
                                @else
                                    <span class="label label-warning radius">未知</span>
                                @endif
                            </td>
                            <td class="td-status">
                                @if($withdraw->member_detail->is_member == 1)
                                <span class="label label-success radius">是</span>
                                @else
                                    <span class="label label-warning radius">否</span>
                                @endif
                            </td>
							<td class="td-manage">
                                <a style="text-decoration:none" onClick="member_audit('审核','{{route('admin.finance.member_audit', array('id'=>$withdraw->id))}}','','300')" href="javascript:;" title="审核">
                                    <i class="Hui-iconfont">&#xe6e1;</i>
                                </a>
                                <a style="text-decoration:none" onClick="member_capital('账单','{{route('admin.member.paylog', array('id'=>$withdraw->member_id))}}','900','600')" href="javascript:;" title="账单">
                                    <i class="Hui-iconfont">&#xe628;</i>
                                </a>
                            </td>
						</tr>
					@endforeach
					</tbody>
				</table>
				{{$withdraws->render()}}
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
</script>
@endsection