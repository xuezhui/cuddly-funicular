@extends('Admin.layouts.common')

@section('content')
<section class="Hui-article-box">
    <nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 供应商管理 <span class="c-gray en">&gt;</span> 供应商列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
    <div class="Hui-article">
        <div >
            <div class="pd-20">
                <form class="text-c" method="post" action="/admin/supplier"> 日期范围：
                    <input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})" id="logmin" class="input-text Wdate" style="width:120px;" name="starttime" value="{{$request->input('starttime')}}">
                    -
                    <input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}', maxDate:'%y-%M-%d'})" id="logmax" class="input-text Wdate" style="width:120px;" name="endtime" value="{{$request->input('endtime')}}">
                    <input type="text" name="name" id="" placeholder=" 供应商姓名/手机号" style="width:250px" class="input-text" value="{{$request->input('name')}}">
                    <button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 搜索</button>
                </form>
                <div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"> <a class="btn btn-primary radius" onclick="agent_add('添加供应商','/admin/supplier/add')" href="javascript:;"><i class="Hui-iconfont">&#xe600;</i> 添加供应商</a></span></div>
                <div class="mt-20">
                    <table class="table table-border table-bordered table-bg table-hover table-sort">
                        <thead>
                            <tr class="text-c">
                                <th width="40">ID</th>
                                <th width="60">供应商名</th>
                                <th width="100">手机号</th>
                                <th width="150">销量</th>
                                <th width="150">添加时间</th>
                                <th>备注</th>
                                <th width="100">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(@$supplier)
                            @foreach (@$supplier as $a)
                            <tr class="text-c va-m">
                                <td>{{$a->id}}</td>
                                <td><a onClick="agent_edit('供应商编辑',' /admin/supplier/add?id={{$a->id}}')" href="javascript:;">{{$a->suppliername}}</a></td>
                                <td class="text-l">{{$a->mobile}}</td>
                                <td>{{$a->seller}}</td>
                                <td>{{date('Y-m-d H:i:s',$a->create_date)}}</td>
                                <td>{{$a->note?:'-'}}</td>
                                <td class="td-manage">
                                    <a style="text-decoration:none" class="ml-5" onClick="agent_edit('信息编辑','/admin/supplier/add?id={{$a->id}}')" href="javascript:;" title="编辑"><i class="Hui-iconfont">&#xe6df;</i></a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    
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
     $('.table-sort').dataTable({
        ordering:false
    });
    /*供应商-编辑*/
    function agent_edit(title, url){
        var index = layer.open({
                type: 2,
                title: title,
                content: url,
                area: ['500px', '550px']
        });
        // layer.full(index);
    }
    function agent_add(title, url){
        var index = layer.open({
                type: 2,
                title: title,
                content: url,
                area: ['500px', '550px']
        });
        // layer.full(index);
    }
    function agent_logs(title, url){
        var index = layer.open({
                type: 2,
                title: title,
                content: url,
                area: ['800px', '650px']
        });
    }
</script>
@endsection

