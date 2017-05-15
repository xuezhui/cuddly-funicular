@extends('Admin.layouts.pop')
@section('content')
<div class="cl pd-20" style=" background-color:#5bacb6">
  <img class="avatar size-XL l" id="avatar" src="{{$member->avatar}}">
  <dl style="margin-left:80px; color:#fff">
    <dt><span class="f-18">{{$member->nickname}}</span><!-- <span class="pl-10 f-12">余额：40</span></dt>
    <dd class="pt-10 f-12" style="margin-left:0">这家伙很懒，什么也没有留下</dd>-->
  </dl>
</div>
<div class="pd-20">
  <table class="table">
    <tbody>
      <tr>
        <th class="text-r">手机：</th>
        <td>{{$member->telephone}}</td>
      </tr>
      <tr>
        <th class="text-r">邮箱：</th>
        <td>{{$member->email}}</td>
      </tr>
      <tr>
        <th class="text-r">注册时间：</th>
        <td>{{$member->created_at}}</td>
      </tr>
    </tbody>
  </table>
</div>
    @section('my-js')
    <script type="text/javascript" src="/lib/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="/lib/layer/2.4/layer.js"></script>
    <script type="text/javascript" src="/static/h-ui/js/H-ui.js"></script>
    <script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.page.js"></script>
    @endsection
@endsection
</html>