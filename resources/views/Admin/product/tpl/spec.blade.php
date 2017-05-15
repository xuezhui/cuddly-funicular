<script type="text/javascript" src="/lib/jquery-ui-1.10.3.min.js"></script>
<table class="table">
    <thead>
        <tr>
          
            <td style='width:150px;'>规格名称</td>
            <td>现价</td>
            <td>市场价</td>
            <td>库存</td>
            <th style='width:50px;'></th>
        </tr>
    </thead>
    <tbody id="spec-items">
        @if(count($specs)>0)
        @foreach($specs as $s)
        <tr>
           
            <td>
                <input name="spec_name[]" type="text" class="input-text param_title" value="{{$s->name}}"/>
                <input name="spec_id[]" type="hidden" class="input-text" value="{{$s->id}}"/>
            </td>
            <td>
                <input name="spec_curprice[]" type="number" class="input-text param_value" value="{{$s->cur_price}}" style="width:100%"/>
            </td>
            <td>
                <input name="spec_marketprice[]" type="number" class="input-text param_value" value="{{$s->market_price}}" style="width:100%"/>
            </td>
            <td>
                <input name="spec_stock[]" type="number" class="input-text param_value" value="{{$s->stock}}" style="width:100%"/>
            </td>
            <td>
               
                <a href="javascript:;" class='btn btn-default btn-sm' onclick="deleteSpec(this)" title="删除"><i class='icon Hui-iconfont'>&#xe6a6;</i></a>
                
            </td>
        </tr>
        @endforeach
        @else
        <tr>
           
            <td>
                <input name="spec_name[]" type="text" class="input-text param_title" value=""/>
                <input name="spec_id[]" type="hidden" class="input-text" value=""/>
            </td>
            <td>
                <input name="spec_curprice[]" type="number" class="input-text param_value" value="" style="width:100%" />
            </td>
            <td>
                <input name="spec_marketprice[]" type="number" class="input-text param_value" value="" style="width:100%" />
            </td>
            <td>
                <input name="spec_stock[]" type="number" class="input-text param_value" value="" style="width:100%"/>
            </td>
            <td>
               
                <a href="javascript:;" class='btn btn-default btn-sm' onclick="deleteSpec(this)" title="删除"><i class='icon Hui-iconfont'>&#xe6a6;</i></a>
                
            </td>
        </tr>
        @endif
    </tbody>
    <tbody>
        <tr>
            
            <td colspan="3">
                <a href="javascript:;" id='add-param' onclick="addSpec()" class="btn btn-default"  title="添加参数"><i class='fa fa-plus'></i> 添加规格</a>
            </td>
        </tr>
    </tbody>
</table>
<table class="specitem" style="display:none;">
    <tr>
           
        <td>
            <input name="spec_name[]" type="text" class="input-text param_title" value=""/>
            <input name="spec_id[]" type="hidden" class="input-text" value=""/>
        </td>
        <td>
            <input name="spec_curprice[]" type="number" class="input-text param_value" value="" style="width:100%"/>
        </td>
        <td>
            <input name="spec_marketprice[]" type="number" class="input-text param_value" value="" style="width:100%"/>
        </td>
        <td>
            <input name="spec_stock[]" type="number" class="input-text param_value" value="" style="width:100%"/>
        </td>
        <td>
           
            <a href="javascript:;" class='btn btn-default btn-sm' onclick="deleteSpec(this)" title="删除"><i class='icon Hui-iconfont'>&#xe6a6;</i></a>
            
        </td>
    </tr>
</table>

<script language="javascript">
$(document).ready(function(){
    $("#spec-items").sortable({stop: function(){
                window.optionchanged = true;
            }});
})
    function addSpec() {
        var html = $('.specitem').html();
        html = html.replace("<tbody>","");
        html = html.replace("</tbody>","");
        $("#spec-items").append(html);
        $("#spec-items").sortable({stop: function(){
                window.optionchanged = true;
            }});
        return;
    }
    function deleteSpec(o) {
        $(o).parent().parent().remove();
    }
</script>