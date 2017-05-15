@extends('Admin.layouts.common')

@section('content')
<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 财务中心 <span class="c-gray en">&gt;</span> 提现列表<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<form class="text-c" action="/admin/finance/swithdraw" method="get"> 
				<div class="col-sm-1 inline">
                <span class="select-box">
                  <select class="select" size="1" name="apply_progress">
                    <option value="" selected>审核状态</option>
                      @foreach($apply_progress as $key => $item)
                        <option value="{{$key}}" @if($key==Request::input('apply_progress')) selected @endif>{{$item}}</option>
                      @endforeach
                </select>
                </span>
                </div>
				日期范围：
				<input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})" id="datemin" class="input-text Wdate" style="width:120px;" value="{{Request::input('starttime')}}" name="starttime">
				-
				<input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d'})" id="datemax" class="input-text Wdate" style="width:120px;" value="{{Request::input('endtime')}}" name="endtime">
				<button type="submit" class="btn btn-success radius" id="" name=""><i class="Hui-iconfont">&#xe665;</i> 搜记录</button>
			</form>
			<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"> </span><span class="r">共有数据：<strong>{{$records->total()}}</strong> 条</span> </div>
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
					@foreach($records as $r)
						<tr class="text-c">
							<td>{{$r->id}}</td>
							<td><u style="cursor:pointer" class="text-primary" onclick="member_show('{{$r->member->nickname}}','{{route('admin.member.show', array('id'=>$r->member->id))}}','360','400')">
                                    {{$r->member->nickname}}
                                </u></td>
							<td>{{$r->member->telephone}}</td>
							<td>{{$r->amount}}</td>
							<td>
                                @if($r->card){{$r->card->card_number}}@endif
                            </td>
                            <td>
                                @if($r->card){{$r->card->bank}}@endif
                            </td>
                            <td>
                                @if($r->card){{$r->card->realname}}@endif
                            </td>
							<td>{{long2ip($r->member->last_login_IP)}}</td>
							<td>{{$r->created_at}}</td>
							<td class="td-status">
                                    @if($r->apply_progress == 0)
                                    <span class="label label-danger radius">冻结</span>
                                    @elseif($r->apply_progress == 1)
                                    <span class="label label-primary radius">待审核</span>
                                    @elseif($r->apply_progress == 2)
                                    <span class="label label-success radius">审核通过</span>
                                    @elseif($r->apply_progress == 3)
                                    <span class="label label-warning radius">审核不通过</span>
                                    @else未知
                                    @endif
                            </td>
                            <td class="td-status">
                                @if($r->status == 1)
                                <span class="label label-success radius">正常</span>
                                @elseif($r->status == 2)
                                    <span class="label label-primary radius">草稿</span>
                                @elseif($r->status == 3)
                                    <span class="label label-primary radius">待审</span>
                                @else
                                    <span class="label label-warning radius">未知</span>
                                @endif
                            </td>
                            <td class="td-status">
                                @if($r->member->is_member == 1)
                                <span class="label label-success radius">是</span>
                                @else
                                    <span class="label label-warning radius">否</span>
                                @endif
                            </td>
							<td class="td-manage">
                                <a style="text-decoration:none" onClick="member_audit('审核','{{route('admin.finance.smember_audit', array('id'=>$r->id))}}','','300')" href="javascript:;" title="审核">
                                    <i class="Hui-iconfont">&#xe6e1;</i>
                                </a>
                                <a style="text-decoration:none" onClick="member_capital('账单','{{route('admin.smember.log', array('id'=>$r->member_id))}}','900','600')" href="javascript:;" title="账单">
                                    <i class="Hui-iconfont">&#xe628;</i>
                                </a>
                            </td>
						</tr>
					@endforeach
					</tbody>
				</table>
				{{$records->render()}}
			</div>
		</article>
	</div>
</section>
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript">
	function verify_apply(title,url,id){
		var index = layer.open({
			type: 2,
			title: title,
			content: url+"?id="+id,
			area:['700px','300px']
		});
	}
	//用户账单
	function member_capital(title,url,w,h) {
	    layer_show(title,url,w,h);
	}
	/*用户-审核*/
	function member_audit(title,url,w,h){
		layer_show(title,url,w,h);
	}
</script>
@endsection