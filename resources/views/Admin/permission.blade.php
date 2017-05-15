@extends('Admin.layouts.common')

@section('content')

<section class="Hui-article-box">
    <nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 管理员管理 <span class="c-gray en">&gt;</span> 权限管理 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
    <div class="Hui-article">
        <article class="cl pd-20">
            <div class="text-c">
                <form class="Huiform" action="/admin/permission" method="post" target="_self">
                    <input type="text" class="input-text" style="width:250px" placeholder="权限名称" id="name" name="name">
                    <button type="submit" class="btn btn-success" id="" name=""><i class="Hui-iconfont">&#xe665;</i> 搜权限节点</button>
                </form>
            </div>
            <div class="cl pd-5 bg-1 bk-gray mt-20"> 
                <span class="l">
                    <!--<a href="javascript:;" onclick="datadel()" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a>--> 
                    <a href="javascript:;" onclick="admin_permission_add('添加权限节点', '/admin/addpermission/', '', '500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加权限节点</a>
                </span> 
                <span class="r">共有数据：<strong>{{count($lists)}}</strong> 条</span> </div>
            <table class="table table-border table-bordered table-bg">
                <thead>
                    <tr>
                        <th scope="col" colspan="7">权限节点</th>
                    </tr>
                    <tr class="text-c">
                        <th width="25"><input type="checkbox" name="" value=""></th>
                        <th width="40">ID</th>
                        <th width="200">权限名称</th>
                        <th>权限路由</th>
                        <th width="100">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lists as $key=>$list)
                    <tr class="text-c">
                        <td><input type="checkbox" value="{{$list->id}}" name=""></td>
                        <td>{{$list->id}}</td>
                        <td>{{$list->name}}</td>
                        <td>{{$list->route_name}}</td>
                        <td>
                            <a title="编辑" href="javascript:;" onclick="admin_permission_edit('权限编辑', '/admin/addpermission?id={{$list->id}}', '', '', '500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> 
                            <a title="删除" href="javascript:;" onclick="admin_permission_del(this, '{{$list->id}}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="5">
                            {!! $lists->links() !!}
                        </td><tr>
                </tfoot>
            </table>
        </article>
    </div>
</section>


<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>
<script type="text/javascript">
/*
 参数解释：
 title	标题
 url		请求的url
 id		需要操作的数据id
 w		弹出层宽度（缺省调默认值）
 h		弹出层高度（缺省调默认值）
 */
/*管理员-权限-添加*/
function admin_permission_add(title, url, w, h) {
    layer_show(title, url, w, h);
}
/*管理员-权限-编辑*/
function admin_permission_edit(title, url, id, w, h) {
    layer_show(title, url, w, h);
}

/*管理员-权限-删除*/
function admin_permission_del(obj, id) {
layer.confirm('角色删除须谨慎，确认要删除吗？', function (index) {
if (id == '')
        return false;
$.ajax({
    url:'/admin/delpermission',
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
@endsection