@extends('Admin.layouts.common')

@section('content')
<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 财务中心 <span class="c-gray en">&gt;</span> 提现列表<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<article class="cl pd-20">
			<form class="text-c" action="/admin/finance/withdraw" method="get"> 日期范围：
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
							<th width="80">编号</th>
							<th width="100">提现人</th>
							<th width="90">金额</th>
							<th width="150">提现时间</th>
						</tr>
					</thead>
					<tbody>
					@foreach($records as $r)
						<tr class="text-c">
							<td>{{$r->id}}</td>
							<td>@if($r->member){{$r->member->realname}}@endif</td>
							<td>{{$r->amount}}元</td>
							<td>{{$r->created_at}}</td>
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
@endsection