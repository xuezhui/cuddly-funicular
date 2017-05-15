@extends('Admin.layouts.common')

@section('content')
    <!--_meta 作为公共模版分离出去-->

    <section class="Hui-article-box">
        <nav class="breadcrumb">
            <i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 用户中心 <span class="c-gray en">&gt;</span> 订单列表<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <form class="text-c" method="get" action="/admin/order/index"> 日期范围：
                    
                    <input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})" id="logmin" class="input-text Wdate" style="width:120px;" name="starttime" value="{{Request::input('starttime')}}">
                    -
                    <input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})" id="logmax" class="input-text Wdate" style="width:120px;" name="endtime" value="{{Request::input('endtime')}}">
                    <div class="col-sm-1 inline">
                    <span class="select-box">
                      <select class="select" size="1" name="process">
                        <option selected>订单状态</option>
                        @foreach($process as $key=>$val)
                        <option value="{{$key}}" @if($key==Request::input('process')) selected @endif>{{$val}}</option>
                        @endforeach
                    </select>
                    </span>
                    </div>
                    <button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 搜订单</button>
                </form>
                <div class="cl pd-5 bg-1 bk-gray mt-20">
                <span class="r">共有数据：<strong>{{$list->total()}}</strong> 条</span>
                </div>
                <div class="mt-20">
                    <table class="table table-border table-bordered table-hover table-bg table-sort">
                        <thead>
                        <tr class="text-c">
                            <th width="25"><input type="checkbox" name="" value=""></th>
                            <th width="80">ID</th>
                            <th width="120">客户信息</th>
                            <th width="300">收货地址</th>
                            <th width="90">付款方式</th>
                            <th width="150">支付状态</th>
                            <th width="">物流状态</th>
                            <th width="130">订单总额</th>
                            <th width="130">下单时间</th>
                            <th >操作</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($list as $vo)
                            <tr class="text-c">
                                <td><input type="checkbox" value="1" name=""></td>
                                <td>{{$vo->id}}</td>
                                <td>{{$vo->realname}}</td>
                                <td>{{$vo->address}}</td>
                                <td>{{$vo->payMethod}}</td>
                                <td>{{$vo->payStatus}}</td>
                                <td>{{$vo->logisticsStatus}}</td>
                                <td>{{$vo->total_amount}}</td>
                                <td>{{$vo->created_at}}</td>
                                <td class="td-manage">
                                    @if($vo->logistics_status==0&&$vo->payment_status)
                                    <a style="text-decoration:none" onClick="send('发货','/admin/order/confirmsend',{{$vo->id}})" href="javascript:;" title="发货"><i class="Hui-iconfont">&#xe603;发货</i></a>
                                    @elseif($vo->logistics_status==1||$vo->logistics_status==2)
                                    <a style="text-decoration:none" onClick="send('发货','/admin/order/confirmsend',{{$vo->id}})" href="javascript:;" title="发货"><i class="Hui-iconfont">&#xe603;重新发货</i></a>
                                    <a style="text-decoration:none" onClick="express('查看物流','/admin/order/express',{{$vo->id}})" href="javascript:;" title="查看物流"><i class="Hui-iconfont">&#xe603;查看物流</i></a>
                                    @endif
                                    <a title="编辑" href="javascript:;" onclick="member_edit('编辑','detail?id={{$vo->id}}','4','','510')" class="ml-5" style="text-decoration:none">
                                        查看详情
                                    </a>
                                    
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list->render() !!}
                </div>

            </article>
        </div>

    </section>
    <script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
    <script type="text/javascript">

        function send(title,url,id){
            var index = layer.open({
                type: 2,
                title: title,
                content: url+'?id='+id,
                area:['500px','300px']
            });
        }
        function express(title,url,id){
            var index = layer.open({
                type: 2,
                title: title,
                content: url+'?id='+id,
                area:['500px','400px']
            });
        }
        /*用户-编辑*/
        function member_edit(title,url,id,w,h){
            layer_show(title,url,w,h);
        }
    </script>
    <!--/请在上方写此页面业务相关的脚本-->
    </body>
    </html>

@endsection