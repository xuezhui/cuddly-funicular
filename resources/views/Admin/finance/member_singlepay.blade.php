<!--_meta 作为公共模版分离出去-->
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="renderer" content="webkit|ie-comp|ie-stand">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta http-equiv="Cache-Control" content="no-siteapp" />
	<link rel="Bookmark" href="favicon.ico" >
	<link rel="Shortcut Icon" href="favicon.ico" />
	<!--[if lt IE 9]>
	<script type="text/javascript" src="/lib/html5.js"></script>
	<script type="text/javascript" src="/lib/respond.min.js"></script>
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css" />
	<link rel="stylesheet" type="text/css" href="/lib/Hui-iconfont/1.0.8/iconfont.css" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/style.css" />
    <link rel="stylesheet" href="/static/pagination.css">
	<!--[if IE 6]>
	<script type="text/javascript" src="http://lib.h-ui.net/DD_belatedPNG_0.0.8a-min.js" ></script>
	<script>DD_belatedPNG.fix('*');</script><![endif]-->
	<!--/meta 作为公共模版分离出去-->
<style>
    .Hui-iconfont {
        font-size: 1.3rem;
        color: #21c371;
    }
	.td-manage>a {
		padding-right:3%;
	}
</style>

<section class="">
	<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页
        <span class="c-gray en">&gt;</span> 会员提现申请 <span class="c-gray en">&gt;</span> 用户账单列表
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
							<th width="20">编号</th>
							<th width="80">用户</th>
							<th width="80">用户手机</th>
							<th width="80">金额</th>
							<th width="80">备注</th>
							<th width="80">创建时间</th>
							<th width="80">修改时间</th>
						</tr>
					</thead>
					<tbody>
					@foreach($logs as $log)
						<tr class="text-c">
							<td>{{$log->id}}</td>
							<td>{{$log->user->nickname}}</td>
							<td>{{$log->user->telephone}}</td>
							<td>
								{{$capital_type[$log->capital_type]}}
								{{$log->amount}}
							</td>
							<td>{{$log->note}}</td>
							<td>{{$log->created_at}}</td>
							<td>{{$log->updated_at}}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
				{{$logs->render()}}
			</div>
		</article>
	</div>
</section>

<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>