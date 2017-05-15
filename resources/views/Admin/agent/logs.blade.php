@extends('Admin.layouts.pop')

@section('content')
<link rel="stylesheet" href="/static/pagination.css">
<title>代理人资金流水</title>
</head>
<body>
	<div class="page-container">
		<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="r">共有数据：<strong>{{$logs->total()}}</strong> 条</span> </div>
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
</body>

@endsection
