@extends('Admin.layouts.common')

@section('content')
<section class="Hui-article-box">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 代理人管理 <span class="c-gray en">&gt;</span> 代理人列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
	<div class="Hui-article">
		<div >
			<div class="pd-20">
				<form class="text-c" method="get" action="/admin/agent/logs"> 日期范围：
					<input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})" id="logmin" class="input-text Wdate" style="width:120px;" name="starttime" value="{{Request::input('starttime')}}">
					-
					<input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})" id="logmax" class="input-text Wdate" style="width:120px;" name="endtime" value="{{Request::input('endtime')}}">
					
					<button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 搜记录</button>
				</form>
				
				<div class="mt-20">
					<table class="table table-border table-bordered table-bg table-hover table-sort">
						<thead>
							<tr class="text-c">
								<th width="40">ID</th>
								<th width="60">来源</th>
								<th width="100">金额</th>
								<th width="150">时间</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($logs as $l)
							<tr class="text-c va-m">
								<td>{{$l->id}}</td>
								<td>{{$l->member}}</td>
								<td class="text-l">@if($l->capital_type==1)+@else-@endif{{$l->amount}}元</td>
								<td>{{$l->created_at}}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					{!! $logs->render() !!}
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

</script>
@endsection

