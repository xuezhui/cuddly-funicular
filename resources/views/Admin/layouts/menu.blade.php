
<aside class="Hui-aside">

    <div class="menu_dropdown bk_2">
      @if(Session::get('parentMenu'))
       @foreach(Session::get('parentMenu') as $key=>$list)
        <dl>
            <dt @if(array_key_exists($list['id'],Session::get('childMenu'))&&in_array('/'.Request::path(),
                 array_reduce(Session::get('childMenu')[$list['id']], create_function('$result, $v', '$result[] = $v["url"];return $result;'))
                 ))class="selected" @endif><i class="Hui-iconfont">{{$list['icon']}}</i> {{$list['name']}}<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd @if(array_key_exists($list['id'],Session::get('childMenu'))&&in_array('/'.Request::path(),
                 array_reduce(Session::get('childMenu')[$list['id']], create_function('$result, $v', '$result[] = $v["url"];return $result;'))
                 ))style="display: block;" @endif>
                <ul>
                    @if(array_key_exists($list['id'],Session::get('childMenu'))&&Session::get('childMenu')[$list['id']])
                   @foreach(Session::get('childMenu')[$list['id']] as $ky=>$menu)
                    <li @if(Request::getRequestUri() == $menu['url']) class="current"@endif><a href="{{$menu['url']}}" title="{{$menu['name']}}">{{$menu['name']}}</a></li>
                    @endforeach
                    @endif
                </ul>
            </dd>
        </dl>
       
       @endforeach
        @endif
       
    </div>
</aside>
<div class="dislpayArrow hidden-xs"><a class="pngfix" href="javascript:void(0);" onClick="displaynavbar(this)"></a></div>