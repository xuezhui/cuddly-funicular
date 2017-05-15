@extends('Admin/layouts/common')

@section('content')

<section class="Hui-article-box">
    <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/admin/" class="maincolor">首页</a> 
        <span class="c-999 en">&gt;</span>
        <span class="c-666">我的桌面</span> 
        <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
    <div class="Hui-article">
        <article class="cl pd-20">
            @if(Session::get('admin_role')->name == '超级管理员')
            <table class="table table-border table-bordered table-bg">
                <thead>
                <tr>
                    <th colspan="7" scope="col">信息统计</th>
                </tr>
                <tr class="text-c">
                    <th>统计</th>
                    <th>总充值业绩</th>
                    <th>已消费券总额</th>
                    <th>总公司基金余额</th>
                    <th>总现金余额</th>
                </tr>
                </thead>
                <tbody>
                <tr class="text-c">
                    <td>总数</td>
                    <td>{{$count_charge}}</td>
                    <td>{{$count_pay}}</td>
                    <td>{{$count_fund}}</td>
                    <td>{{$count_cash}}</td>
                </tr>
                </tbody>
            </table>

            <table class="table table-border table-bordered table-bg">
                <thead>
                <tr>
                    <th colspan="7" scope="col">子公司信息统计</th>
                </tr>
                <tr class="text-c">
                    <th>统计</th>
                    <th>子公司总充值</th>
                </tr>
                </thead>
                <tbody>
                @foreach($count_agents as $name => $count)
                <tr class="text-c">
                    <td>{{$name}}</td>
                    <td>{{$count}}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
                @endif
        </article>

    </div>
</section>



@endsection