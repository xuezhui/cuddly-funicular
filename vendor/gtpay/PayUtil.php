<?php
/**
 * 通联支付 接口
 * @author Change
 * @date 2017-4-14
 *
 */

class PayUtil{

    public $payUrl = "https://cashier.allinpay.com/mobilepayment/mobile/SaveMchtOrderServlet.action";//支付服务链接

    public $userRegUrl = "https://service.allinpay.com/usercenter/merchant/UserInfo/reg.do"; //用户注册链接

    public $inputCharset = "1";//字符集  1 代表 UTF-8、2 代表 GBK、3 代表GB2312
    // private $pickupUrl = "";//同步returnURL
    // private $receiveUrl = "";//异步NotifyURL
    public $version = "v1.0";//网关接收支付请求接口版本 固定填 v1.0
    public $language = 1;//网关页面显示语言种类 1 代表简体中文2 代表繁体中文3 代表英文
    public $signType = 0;//签名类型  默认填 0，固定选择值：0、1；0 表示订单上送和交易结果通知都使用 MD5 进行签名;1 表示商户用使用 MD5 算易结果通知使用证书签名
    public $merchantId = "";//商户号
    // private $orderNo = "";//商户订单号
    // private $orderAmount = 0;//商户订单金额 整型数字 单位为分
    public $orderCurrency = 0; //订单金额币种类型 0 和 156 代表人民币、840 代表美元、344 代表港币
    // private $orderDatetime = "";//商户订单提交时间 日期格式：yyyyMMDDhhmmss，例如：20121116020101
    // private $ext1 = "";//扩展字段 1 按照"ext1":"<USER>userId</USER>"格式进行上传。
    public $payType = 0; //支付方式 33 手机网页 H5 支付

    public $key = ""; //用于计算signMsg的key值 从平台获取
    // private $partnerUserId = 0;// 本应用中用户ID
    /**
     * 支付构造函数，用于初始化数据
     * @param  $merchantId    [商户号]
     * @param  $key           [用于计算signMsg的key值 从平台获取]
     */
    public function __construct($merchantId,$key)
    {
        $this->merchantId = $merchantId;
        $this->key = $key;

    }

    /**
     * 获取支付签名
     * @param  $pickupUrl       [同步returnURL]
     * @param  $receiveUrl   [异步NotifyURL]
     * @param  $orderNo       [商户订单号]
     * @param  $orderAmount   [商户订单金额]
     * @param  $orderDatetime   [商户订单提交时间]
     * @param  $ext1          [用户信息 通过表单提交而来]
     * @param  $orderCurrency [订单金额币种类型 默认为0]
     */
    public function getRedirectUrl($pickupUrl,$receiveUrl,$orderNo,$orderAmount,$orderDatetime,$ext1,$orderCurrency=0)
    {
        $src = "";
        $src .= "inputCharset=".$this->inputCharset."&";
        $src .= "pickupUrl=".$pickupUrl."&";
        $src .= "receiveUrl=".$receiveUrl."&";
        $src .= "version=".$this->version."&";
        $src .= "language=".$this->language."&";
        $src .= "signType=".$this->signType."&";
        $src .= "merchantId=".$this->merchantId."&";
        $src .= "orderNo=".$orderNo."&";
        $src .= "orderAmount=".$orderAmount."&";
        $src .= "orderCurrency=".$orderCurrency."&";
        $this->orderCurrency = $orderCurrency;
        $src .= "orderDatetime=".$orderDatetime."&";
        $src .= "productName=Recharge&";
        $src .= "ext1=".$ext1."&";
        $src .= "payType=".$this->payType."&";

        $src1 = $src."key=".$this->key;
        $sign = trim(strtoupper(md5($src1)));
        $src = $this->payUrl.'?'.$src.'signMsg='.$sign;
        return $src;
    }

    /**
     * 获取注册签名
     * @param  $partnerUserId    [本应用中用户ID]
     */
    private function getRegSign($partnerUserId=0)
    {
        //原串 的首尾要加上 &
        $src = "&";
        $src .= "signType=".$this->signType."&";
        $src .= "merchantId=".$this->merchantId."&";
        $src .= "partnerUserId=".$partnerUserId."&";
        $src .= "key=".$this->key;
        $src .= "&";
        return trim(strtoupper(md5($src)));
    }

    /**
     * 获取通联用户ID
     * @param  integer $partnerUserId [本应用中用户ID]
     * @return [Array]                 [结果]
     */
    public function getTLUserid($partnerUserId=0)
    {
        require("Communication.php");
        $signMsg = $this->getRegSign($partnerUserId);
        $data = Communication::send($this->userRegUrl,array('signType'=>$this->signType,'merchantId'=>$this->merchantId,'partnerUserId'=>$partnerUserId,'signMsg'=>$signMsg),'post');
        $data = json_decode($data);
        if($data){
            if($data->resultCode=='0000'||$data->resultCode=='0006'){
                //返回通联用户ID 可保存在数据库中
                return array('status'=>1,'userId'=>$data->userId);
            }
            //返回错误信息
            return array('status'=>0,'msg'=>$data->describe);
        }
        //访问不成功;
        return array('status'=>0,'msg'=>'访问错误');
    }

    /**
     * 支付结果解析
     * @param  [type] $payAmount [description]
     * @return [type]            [description]
     */
    public function payResult($paymentOrderId,$orderNo,$orderDatetime,$orderAmount,$payDatetime,$payAmount,$ext1,$payResult,$returnDatetime,$signMsg)
    {


        $bufSignSrc="";
        $bufSignSrc=$bufSignSrc."merchantId=".$this->merchantId."&";

        $bufSignSrc=$bufSignSrc."version=".$this->version."&";

        $bufSignSrc=$bufSignSrc."language=".$this->language."&";

        $bufSignSrc=$bufSignSrc."signType=".$this->signType."&";

        $bufSignSrc=$bufSignSrc."payType=".$this->payType."&";
        if($paymentOrderId != "")
            $bufSignSrc=$bufSignSrc."paymentOrderId=".$paymentOrderId."&";
        if($orderNo != "")
            $bufSignSrc=$bufSignSrc."orderNo=".$orderNo."&";
        if($orderDatetime != "")
            $bufSignSrc=$bufSignSrc."orderDatetime=".$orderDatetime."&";
        if($orderAmount != "")
            $bufSignSrc=$bufSignSrc."orderAmount=".$orderAmount."&";
        if($payDatetime != "")
            $bufSignSrc=$bufSignSrc."payDatetime=".$payDatetime."&";
        if($payAmount != "")
            $bufSignSrc=$bufSignSrc."payAmount=".$payAmount."&";
        if($ext1 != "")
            $bufSignSrc=$bufSignSrc."ext1=".$ext1."&";
        if($payResult != "")
            $bufSignSrc=$bufSignSrc."payResult=".$payResult."&";
        if($returnDatetime != "")
            $bufSignSrc=$bufSignSrc."returnDatetime=".$returnDatetime;

        if($signMsg == strtoupper(md5($bufSignSrc."&key=".$this->key)))
        {
            $verifyResult = 1;
        }
        else
        {
            $verifyResult = 0;
        }
        return $verifyResult;
    }
}