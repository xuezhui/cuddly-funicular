<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\M3Result;
use DB;
use Request;
use App\Models\ErrorCode;

/**
 * Description of ExpressController
 *
 * @author chensq
 * @time 2017-4-26 11:30
 */
class ExpressController extends Controller {

    /**
     * 
     * @param type $params 
     * $params json_encode(array("com"=>'','num'=>''))
     * @return type
     */
    static function poll($params) {
        //参数设置
        $post_data = array();
        $post_data["customer"] = '';
        $key = '';
        $post_data["param"] = json_encode($params); //'{"com":"yuantong","num":"883694722577193295","from":"","to":""}';

        $url = 'https://poll.kuaidi100.com/poll/query.do';
        $post_data["sign"] = md5($post_data["param"] . $key . $post_data["customer"]);

        $post_data["sign"] = strtoupper($post_data["sign"]);
        $o = "";
        foreach ($post_data as $k => $v) {
            $o .= "$k=" . $v . "&";  //默认UTF-8编码格式
        }
        $post_data = substr($o, 0, -1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名 
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    //put your code here
}
