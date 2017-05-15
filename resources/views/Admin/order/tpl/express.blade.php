@extends('Admin.layouts.pop')

@section('content')
<style type="text/css">
	ul{
		list-style-type:circle;
		margin: 10px;
		font-size: 14px;
		margin-left:25px;
	}
	ul li{
		border-bottom:2px solid #eee;
		margin-top:5px;
	}
	ul li:first-child{
		color:red;
	}
</style>
<title>物流</title>
</head>
<body>
	<div class="page-container">
		<ul>
			@foreach($express as $e)
				<li>
					{{$e['context']}}<br>{{$e['time']}}
				</li>
			@endforeach
		</ul>
	</div>
</body>

@endsection
