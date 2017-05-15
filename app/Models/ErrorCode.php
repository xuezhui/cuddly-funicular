<?php
namespace App\Models;
/**
 * Created by PhpStorm.
 * User: Zhiheind
 * Date: 2017/3/23
 * Time: 13:16
 *

//** 专用的ERROR_CODE类，同时提供了描述文本 */
class ErrorCode
{
    public static $OK                     = [0,  '',            ''];
    public static $FAIL                   = [-1,  '操作失败',            'FAIL'];
    public static $UNKNOWN_ERROR          = [1,  '未知错误',            'unknow error'];
    public static $DB_ERROR               = [2,  '数据库错误',          'database error'];
    public static $PARAM_ERROR            = [3,  '请求参数错误',        'param error'];
    public static $DATA_EMPTY             = [4,  '数据不存在',          'nothing here'];
    public static $NO_AUTH                = [5,  '没有权限操作',        'no promise here'];
    public static $NOT_USER               = [6,  '没有登录，不可操作。',  'no login no promise'];
    public static $NOT_MODEL              = [7,  '错误的模型对象',       'model error'];
    public static $NO_FILE_UPLOAD         = [8,  '没有发现上传文件',     'no file found'];
    public static $INVALID_USER_ID        = [9,  '错误的用户信息',       'user info unkonw'];
    public static $NO_CHANGE_FOUND        = [10, '未发现数据更新',       'no change found'];

    public static $ONLY_GET_ALLOW         = [11, '错误，此处只接受GET数据。',       'only get allow here'];
    public static $ONLY_POST_ALLOW        = [12, '错误，此处只接受POST数据。',       'only post allow here'];
    public static $ONLY_USER_ALLOW        = [13, '您需要登录后才可以执行该操作。',       'need login'];
    public static $REQUEST_TIME_OUT       = [14, '请求失败了，请检查你的网络状态和系统时间是否准确哦。',       'request is out of time'];
    public static $SIGNATURE_WRONG        = [15, '校验失败',       'SIGNATURE_WRONG'];
    public static $ORDER_VALUE_ERROR      = [16, '请使用正确的排序方案。',       'ORDER_VALUE_ERROR'];
    public static $NO_TBALE_FOUND         = [17, '没有对应的表存在',       'NO_TBALE_FOUND'];
    public static $ONLY_ADMIN_ALLOW       = [18, '仅限管理员使用此功能。',       'ONLY_ADMIN_ALLOW'];
    public static $ONLY_VISITOR_ALLOW     = [19, '您已登录，不可重复登录。',       'ONLY_VISITOR_ALLOW'];
    public static $UNKNOWN_API_ACTION     = [20, '错误的请求地址，不可使用。',       'UNKNOWN_API_ACTION'];
    public static $TELEPHONE_INVALID      = [21, '不合法的手机号',       'TELEPHONE_INVALID'];
    public static $PASSWORD_TOO_SHORT     = [22, '密码太短',       'PASSWORD_TOO_SHORT'];
    public static $QUERYPWD_NOT_SAME_PWD  = [23, '确认密码和密码不一致',       'QUERYPWD_NOT_SAME_PWD'];
    public static $FILE_TOO_LARGE         = [24, '上传文件过大',       'FILE_TOO_LARGE'];
    public static $ONLY_GET_REQUEST       = [25, '只接受GET请求',       'ONLY_GET_REQUEST'];
    public static $ONLY_POST_REQUEST      = [26, '只接受POST请求',       '$ONLY_POST_REQUEST'];
    public static $NO_RECOMMENDER         = [27, '无推荐人',       'NO_RECOMMENDER'];
    public static $ALREADY_EXIST_DEFAULT  = [28, '已经存在默认地址',       'ALREADY_EXIST_DEFAULT'];
    public static $REGISTRT_FAIL_IN_PAY   = [29, '在第三方支付平台注册失败',       'REGISTRT_FAIL_IN_PAY'];
    public static $NOT_ALLOW_AMOUNT       = [30, '不允许的充值金额',       'NOT_ALLOW_AMOUNT'];
    public static $AMOUNT_POSSIBLE_TAMPER = [31, '充值金额可能被篡改',       'AMOUNT_POSSIBLE_TAMPER'];

    //实际开发过程中，可以继续自定义更多的错误类型
    public static $LOGLIST_TYPE_WRONG        = [101, '错误的日志类型',       'LOGLIST_TYPE_WRONG'];
    public static $LOGLIST_NO_LOG_FOUND      = [102, '暂无相关日志',       'LOGLIST_NO_LOG_FOUND'];

    public static $USER_PLS_OLD_PWD          = [111, '请输入当前密码，您才可以继续执行操作。',       'USER_PLS_NEW_PWD'];
    public static $USER_WRONG_OLD_PWD        = [112, '当前密码错误，您不可以执行此操作。',       'USER_WRONG_OLD_PWD'];
    public static $USER_PLS_NEW_PWD          = [113, '您必须指定一个新的密码。',       'USER_PLS_NEW_PWD'];
    public static $USER_DUP_USERNAME         = [114, '该用户名已存在。',       'USER_DUP_USERNAME'];
    public static $USER_DUP_TELEPHONE        = [114, '该手机号已存在。',       'USER_DUP_TELEPHONE'];
    public static $USER_DUP_EMAIL            = [114, '该邮箱已存在。',       'USER_DUP_EMAIL'];
    public static $USER_LOGIN_FAIL           = [115, '登录失败，账号或密码错误。',       'USER_LOGIN_FAIL'];
    public static $USER_BEEN_DISABLED        = [116, '该账号已被禁用。',       'USER_BEEN_DISABLED'];
    public static $USER_PLS_ACCOUNT          = [117, '请输入登录账号',       'USER_PLS_ACCOUNT'];
    public static $USER_PLS_PWD              = [118, '请输入密码',       'USER_PLS_PWD'];
    public static $USER_USED_UNION           = [119, '该联合登录已被绑定',       'USER_USED_UNION'];
    public static $USER_UNAME_NO_PHONE       = [120, '不可以使用手机号作为用户名。',       'USER_UNAME_NO_PHONE'];
    public static $USER_UNAME_NO_EMAIL       = [121, '不可以使用邮箱作为用户名。',       'USER_UNAME_NO_EMAIL'];
    public static $SMS_TOO_OFEN              = [122, '验证码发送太频繁，请稍后再试。',       'SMS_TOO_OFEN'];
    public static $SMS_PHONE_EXISTS          = [123, '该手机号已存在，不可用于注册。',       'SMS_PHONE_EXISTS'];
    public static $SMS_PHONE_INVAILD         = [124, '该手机号并未注册过，无法找回密码哦。',       'SMS_PHONE_INVAILD'];
    public static $SMS_PLS_USEFOR            = [125, '发送验证码必须要有用途说明哦',       'SMS_PLS_USEFOR'];
    public static $SMS_VERIFYCODE_WRONG      = [126, '验证码错误',       'SMS_VERIFYCODE_WRONG'];
    public static $SMS_NO_PHONE_FOUND        = [127, '该手机号并未注册过，无法用于登录哦。',       'SMS_NO_PHONE_FOUND'];
    public static $DEVICE_PLS_TOKEN          = [129, '请输入正确的设备号。',       'DEVICE_PLS_TOKEN'];
    public static $DEVICE_PLS_USER_OR_PHONE  = [130, '请指定用户的ID或手机号',       'DEVICE_PLS_USER_OR_PHONE'];
    public static $DEVICE_PLS_TYPE           = [131, '请使用指定的推送方式',       'DEVICE_PLS_TYPE'];
    public static $AMAP_UNKONW_LOCATION      = [132, '无法根据地址找到您的坐标，或定位范围太过模糊，请完善您的地址信息再试。',       'AMAP_UNKONW_LOCATION'];
    public static $SMS_PHONE_IS_BIND         = [133, '该手机号已存在，不可用于绑定。',       'SMS_PHONE_IS_BIND'];
    public static $CAPTCHA_CODE_WRONG        = [134, '验证码错误或已失效',       'CAPTCHA_CODE_WRONG'];
    public static $SMS_VERIFYCODE_TIMEOUT    = [135, '验证码已失效，请重新获取',       'SMS_VERIFYCODE_TIMEOUT'];
    public static $USER_LOGIN_IP_TOO_MORE    = [136, '因为安全原因，您此次登录失败，请休息一段时间再登录吧。',       'USER_LOGIN_IP_TOO_MORE'];
    public static $USER_LOGIN_USER_TOO_MORE  = [137, '因为安全原因，您此次登录失败，请休息一段时间再登录吧。',       'USER_LOGIN_USER_TOO_MORE'];
    public static $USER_SMS_IP_TOO_MORE      = [138, '因为安全原因，您此次验证码发送失败，请休息一段时间再试。',       'USER_SMS_IP_TOO_MORE'];
    public static $USER_SMS_TEL_TOO_MORE     = [139, '因为安全原因，您此次验证码发送失败，请休息一段时间再试。',       'USER_SMS_TEL_TOO_MORE'];
    public static $SEND_SMS_ERROR            = [140, '发送短信验证码失败',       'SEND_SMS_ERROR'];
    public static $PID_INVALID               = [141, '推广链接的PID有误',       'PID_INVALID'];
    public static $DATE_FORMAT_INVALID       = [142, '日期格式有误',       'DATE_FORMAT_INVALID'];

    //订单相关
    public static $GOOD_ID_INVALID           = [143, '商品ID不合法',       'GOOD_ID_INVALID '];
    public static $ORDER_ID_INVALID          = [143, '订单ID不合法',       'ORDER_ID_INVALID '];
    public static $MONEY_INSUFFICIENT        = [144, '购物券余额不足',       'MONEY_INSUFFICIENT '];
    public static $PAY_PASSWORD_INVALID      = [145, '支付密码有误',       'PAY_PASSWORD_INVALID '];
    public static $UNAUTHORIZED              = [146, '用户未授权',       'UNAUTHORIZED '];
    public static $USER_NOT_EXSIT            = [147, '用户不存在',       'USER_NOT_EXSIT '];

    public static $STOCK_INSUFFICIENT            = [600, '库存不足',       'STOCK_INSUFFICIENT'];
    public static $CASH_INSUFFICIENT            = [601, '余额不足',       'CASH_INSUFFICIENT'];
    public static $WITHDRAWAL_CASH_INVALID            = [602, '提现金额须是100的整数倍',   'CASH_INVALID'];
    public static $CARD_INVALID            = [603, '无效的银行卡信息',   'CARD_INVALID'];
    public static $DUPLICATE_CARD            = [604, '银行卡信息重复',   'DUPLICATE_CARD'];
    public static $INCOMPLETE_DATA            = [605, '数据不完整',   'INCOMPLETE_DATA'];
    public static $EMPTY_EXPRESS_DATA            = [606, '查询无结果，请隔段时间再查',   'EMPTY_EXPRESS_DATA'];
    //如需更多错误码，请在以下延伸，建议使用四位数以上数字作为您的错误码。
    //后台权限
    public static $ADMIN_NOT_EXIST           =[1001,'管理账号不存在','ADMIN_NOT_EXIST'];
    public static $ADMINROLE_NOT_EXIST       =[1002,'管理账号无角色','ADMINROLE_NOT_EXIST'];
    public static $ADMINPERMISSION_NOT_EXIST =[1003,'管理账号无权限','ADMINPERMISSION_NOT_EXIST'];

    //服务会员错误码
    public static $SERVER_USER_INVAILD       = [1004,'服务会员不存在','SERVER_USER_INVAILD'];
    public static $SERVER_USER_PEDDING       = [1005,'服务会员权限正在审核','SERVER_USER_PEDDING'];
    public static $SERVER_USER_REJECT        = [1006,'服务会员权限审核不通过','SERVER_USER_REJECT'];
    public static $SERVER_USER_ALREADY       = [1007,'用户已经是服务会员','SERVER_USER_ALREADY'];
}