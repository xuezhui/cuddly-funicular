@extends('Admin.layouts.common')
@section('my-css')
    <style>
        a.del {
            font-size: medium;
            font-family: "Helvetica Neue", Helvetica, "Segoe UI", Arial, freesans, sans-serif;
        }
    </style>
@endsection
@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页
            <span class="c-gray en">&gt;</span> 配置文件管理 <span class="c-gray en">&gt;</span> 充值额度配置
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
                <i class="Hui-iconfont">&#xe68f;</i></a></nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div class="cl pd-5 bg-1 bk-gray mt-20">
                    <span class="l">
                        <a class="btn btn-primary radius" onclick="amount_add('添加额度','{{route("admin.config.amountAdd")}}',800,200)" href="javascript:;">
                            <i class="Hui-iconfont">&#xe600;</i> 新增
                        </a>
                      </span>
                </div>
                <div class="panel panel-secondary mt-20">
                    <div class="panel-header">充值额度</div>
                    <div class="panel-body">
                        @foreach($config as $item)
                            <button class="btn btn-warning-outline radius">{{$item}} <a href="javascript:;" onclick="amount_del('{{$item}}')" class="del" title="点我删除">x</a></button>
                        @endforeach

                    </div>
                </div>
            </article>
        </div>
    </section>
    <script type="text/javascript">
        function amount_add(title,url,w,h) {
            layer_show(title,url,w,h);
        }
        function amount_del(amount) {
            layer.confirm('是否确认删除金额:'+amount, function () {
                $.post("{{route('admin.config.amountDel')}}",{amount:amount,_method:'delete'}, function (res) {
                    if (res.errorCode == 0) {
                        layer.msg('删除成功',{icon:6,time:1000}, function () {
                            window.location.reload();
                        });
                    } else {
                        layer.msg(res.errorStr+' '+res.results,{icon:5,time:2000}, function () {
                            window.location.reload();
                        });
                    }
                }, 'json');
            }),function () {}
        }
    </script>
@endsection