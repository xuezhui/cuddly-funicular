@extends('Admin.layouts.pop')
@section('content')
    <article class="cl pd-20">
        <form method="post" action="javascript:;" class="form form-horizontal" id="form-amount">
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>额度：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" placeholder="" name="amount" required>
                </div>
            </div>
            <div class="row cl">
                <div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
                    <input class="btn btn-primary radius" type="submit" onclick="amount_put('form-amount')" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
                </div>
            </div>
        </form>
    </article>
    <script type="text/javascript">
        function amount_put(form_id) {
            $.post("{{route('admin.config.amountPut')}}",$("#"+form_id).serialize(), function (res) {
                if (res.errorCode == 0) {
                    layer.msg('添加成功',{icon:6,time:1000}, function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.location.reload();
                        parent.layer.close(index);
                    });
                } else {
                    layer.msg(res.errorStr+' '+res.results,{icon:5,time:2000}, function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.location.reload();
                        parent.layer.close(index);
                    });
                }
            }, 'json');
        }
    </script>
@endsection