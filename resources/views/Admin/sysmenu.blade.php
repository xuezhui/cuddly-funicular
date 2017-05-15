@extends('Admin.layouts.common')
@section('content')
<section class="Hui-article-box">
    <nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 系统管理 <span class="c-gray en">&gt;</span> 栏目管理 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
    <div class='Hui-article'>
        <article class="cl pd-20">
            <div class="pd-20 text-c">
                <div class="text-c">
                    <!--                    <form class="Huiform" action="/admin/sysmenu" method="get" target="_self">
                                            <input type="text" name="name" id="" placeholder="栏目名称、id" style="width:250px" class="input-text">
                                            <button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 搜索</button>
                                        </form>-->
                </div>
                <div class="cl pd-5 bg-1 bk-gray mt-20"> 
                    <span class="l">
                        <!--<a href="javascript:;" onclick="datadel()" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a>--> 
                        <a class="btn btn-primary radius" onclick="system_category_add('添加资讯', '/admin/addsysmenu', '500', '450')" href="javascript:;"><i class="Hui-iconfont">&#xe600;</i> 添加栏目</a></span> 

                </div>
                <div class="mt-20" id="menu_content">
                    <table class="table table-border table-bordered table-hover table-bg table-sort">
                        <thead>
                            <tr class="text-c">

                                <th width="80">ID</th>
                                <th>栏目名称</th>
                                <th>栏目地址</th>
                                <th>icon</th>
                                <th width="80">排序</th>
                                <th width="100">操作</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach(@$lists[0] as $key=>$list)
                            <tr class="text-c">
                                <td>{{$list['id']}}</td>
                                <td class="text-l">@if($list['level']>0)&nbsp;&nbsp;├&nbsp;@endif{{$list['name']}}</td>
                                <td>{{$list['url']}}</td>
                                <td><i class="Hui-iconfont">{{$list['icon']}}</i> </td>
                                <td>{{$list['sort']}}</td>

                                <td class="f-14"><a title="编辑" href="javascript:;" onclick="system_category_edit('栏目编辑', '/admin/addsysmenu?id={{$list["id"]}}', '', '500', '450')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="system_category_del(this, '{{$list['id']}}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
                            </tr>
                            @if(@$lists[$list['id']])
                            @foreach(@$lists[$list['id']] as $k=>$l)
                            <tr class="text-c">
                                <td>{{$l['id']}}</td>
                                <td class="text-l">@if($l['level']>0)&nbsp;&nbsp;├&nbsp;@endif{{$l['name']}}</td>
                                <td>{{$l['url']}}</td>
                                <td><i class="Hui-iconfont">{{$l['icon']}}</i> </td>
                                <td>{{$l['sort']}}</td>

                                <td class="f-14"><a title="编辑" href="javascript:;" onclick="system_category_edit('栏目编辑', '/admin/addsysmenu?id={{$l["id"]}}', '', '500', '450')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="system_category_del(this, '{{$l['id']}}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
                            </tr>
                            @endforeach
                            @endif
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </article>
    </div>
</section>


<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>
<script type="text/javascript">
    $('.table-sort').dataTable({
        ordering:false
    });
    /*系统-栏目-添加*/
    function system_category_add(title, url, w, h) {
    layer_show(title, url, w, h);
    }
    /*系统-栏目-编辑*/
    function system_category_edit(title, url, id, w, h) {
    layer_show(title, url, w, h);
    }
    /*系统-栏目-删除*/
    function system_category_del(obj, id) {
    layer.confirm('确认要删除吗？', function (index) {
    if (id == '')
            return false;
    $.ajax({
    url:'/admin/delsysmenu',
            type:'post',
            dataType:'json',
            data:{'id':id},
            success:function(rs){
            if (typeof (rs) == 'undefined'){
            layer.msg('系统错误!0', {icon: 5, time: 1000});
            return false;
            }
            if (rs.errorCode == 0){
            $(obj).parents("tr").remove();
            layer.msg('已删除!', {icon: 1, time: 1000});
            } else
                    layer.msg('执行失败!', {icon: 5, time: 1000});
            }
    });
    });
    }

</script>

<!--/请在上方写此页面业务相关的脚本-->
@endsection