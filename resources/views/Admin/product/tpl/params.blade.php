<script type="text/javascript" src="/lib/jquery-ui-1.10.3.min.js"></script>
<table class="table">
    <thead>
        <tr>
          
            <td style='width:150px;'>参数名称</td>
            <td>参数值 </td>
            <th style='width:50px;'></th>
        </tr>
    </thead>
    <tbody id="param-items">
        @if(count($attrs)>0)
        @foreach($attrs as $a)
        <tr>
           
            <td>
                <input name="param_title[]" type="text" class="input-text param_title" value="{{$a->attrkey}}"/>
                <input name="param_id[]" type="hidden" class="input-text" value="{{$a->id}}"/>
            </td>
            <td>
                <input name="param_value[]" type="text" class="input-text param_value" value="{{$a->attrvalue}}"/>
            </td>
            <td>
               
                <a href="javascript:;" class='btn btn-default btn-sm' onclick="deleteParam(this)" title="删除"><i class='icon Hui-iconfont'>&#xe6a6;</i></a>
                
            </td>
        </tr>
        @endforeach
        @else
        <tr>
           
            <td>
                <input name="param_title[]" type="text" class="input-text param_title" value=""/>
                <input name="param_id[]" type="hidden" class="input-text" value=""/>
            </td>
            <td>
                <input name="param_value[]" type="text" class="input-text param_value" value=""/>
            </td>
            <td>
               
                <a href="javascript:;" class='btn btn-default btn-sm' onclick="deleteParam(this)" title="删除"><i class='icon Hui-iconfont'>&#xe6a6;</i></a>
                
            </td>
        </tr>
        @endif
    </tbody>
    <tbody>
        <tr>
            
            <td colspan="3">
                <a href="javascript:;" id='add-param' onclick="addParam()" class="btn btn-default"  title="添加参数"><i class='fa fa-plus'></i> 添加参数</a>
            </td>
        </tr>
    </tbody>
</table>
<table class="item" style="display:none;">
    <tr>
           
        <td>
            <input name="param_title[]" type="text" class="input-text param_title" value=""/>
            <input name="param_id[]" type="hidden" class="input-text" value=""/>
        </td>
        <td>
            <input name="param_value[]" type="text" class="input-text param_value" value=""/>
        </td>
        <td>
           
            <a href="javascript:;" class='btn btn-default btn-sm' onclick="deleteParam(this)" title="删除"><i class='icon Hui-iconfont'>&#xe6a6;</i></a>
            
        </td>
    </tr>
</table>

<script language="javascript">
$(document).ready(function(){
    $("#param-items").sortable({stop: function(){
                window.optionchanged = true;
            }});
})
    function addParam() {
        var html = $('.item').html();
        html = html.replace("<tbody>","");
        html = html.replace("</tbody>","");
        $("#param-items").append(html);
        $("#param-items").sortable({stop: function(){
                window.optionchanged = true;
            }});
        return;
    }
    function deleteParam(o) {
        $(o).parent().parent().remove();
    }
</script>