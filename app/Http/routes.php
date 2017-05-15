<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */

Route::get('/', 'TestController@index');

Route::get('/login','Admin\LoginController@login');

Route::group(['prefix' => 'service', 'middleware' => ['web'], 'namespace' => 'Service'], function () {
    //发送短信验证码
    Route::post('validate_phone/send', 'SmsController@sendSMS')->middleware('allow.origin');
    //单文件上传
    Route::post('raw/upload', 'RawController@uploadFile')->name('service.raw.upload');
    Route::post('alipay', 'PayController@aliPay');
    //微信充值
    Route::post('wxpay', 'PayController@wxPay');

    Route::post('pay/ali_notify', 'PayController@aliNotify');
    Route::get('pay/ali_result', 'PayController@aliResult');
    Route::get('pay/ali_merchant', 'PayController@aliMerchant');

    Route::post('pay/wx_notify', 'PayController@wxNotify');
});

/* * ============================API相关========================================== */

//这些路由不需要登陆就可以访问
Route::group(['prefix' => 'api', 'namespace' => 'Api', 'middleware' => ['web', 'allow.origin']], function ()
{
    //首页服务会员
    Route::post('user/serves', 'UserController@getServerUsers');
    Route::post('user/servedetail', 'UserController@serveDetail');

    //每日专题
    Route::post('activity/list', 'ActivityController@getActList');
    Route::post('activity/detail', 'ActivityController@detail');
    Route::post('activity/add', 'ActivityController@add');
    Route::any('activity/getVersion', 'ActivityController@getVersion');
    Route::match(['get','post'],'activity/image','ActivityController@getDefaultImg');
    // 上传文件
    Route::post('file/upload', 'UserController@uploadAvatar');
    //上传服务商LOGO
    Route::post('file/uploadlogo', 'UserController@uploadLogo');
    //我的推荐人
    Route::any('user/recommend', 'UserController@getRecommend');
    //轮播图
    Route::any('filmslide/list', 'FilmslideController@getFilmslideList');
    //商品列表
    Route::post('product/list', 'ProductController@getProductList');
    //商品詳情
    Route::post('product/detail', 'ProductController@detail');
    //商品新增
    Route::post('product/add', 'ProductController@add');
    //爆款推荐商品
    Route::post('product/explosions', 'ProductController@getExplosions');
    //商品分类列表
    Route::post('category/list', 'CategoryController@getCategoryList');
    //商品分类詳情
    Route::post('category/detail', 'CategoryController@detail');
    //商品分类新增
    Route::post('category/add', 'CategoryController@add');
    //注册
    Route::post('toRegister', 'MemberController@toRegister');
    //登陆
    Route::post('toLogin', 'MemberController@toLogin');
    //用户不登陆修改密码
    Route::post('modifyPassword', 'MemberController@toModPassword');
    //商品规格
    Route::post('pro_spec/list', 'ProSpecController@getProSpecList');
    Route::post('pro_spec/detail', 'ProSpecController@detail');
    Route::post('pro_spec/add', 'ProSpecController@add');
    //获取服务会员店铺信息
    Route::post('getStoreInfo', 'MemberController@shopInfo');

    Route::any('charge/returnUrl', 'UserController@returnUrl');
    Route::any('charge/notify', 'UserController@notify'); 
    Route::any('charge/amount','UserController@getAllowAmount');
});

Route::group(['prefix' => 'api', 'namespace' => 'Api', 'middleware' => ['web', 'allow.origin', 'check.login']], function ()
{
    //用户充值
    Route::any('user/charge', 'UserController@charge');
    
    //用户退出
    Route::post('member/logout', 'MemberController@toLogout');
    //用户资料完善
    Route::post('user/detail', 'UserController@detail');
    Route::post('user/update', 'UserController@update');
    //获取用户详情
    Route::post('getMemberDetail', 'MemberController@memberDetail');

    //申请服务会员
    Route::post('user/applyProvider', 'UserController@applyProvider');

    //收货地址
    Route::post('shop_address/list', 'ShopAddressController@getShopAddressList');
    //订单详情-收货地址详细
    Route::post('receipt/address', 'ShopAddressController@detail');
    Route::post('shop_address/add', 'ShopAddressController@add');
    Route::post('shop_address/update', 'ShopAddressController@update');
    Route::post('shop_address/delete', 'ShopAddressController@delete');

    //生成推广链接的二维码
    Route::post('qrcode/get', 'QrcodeController@generateSpread');
	Route::post('qrcode/geturl', 'QrcodeController@generateSpreadUrl');
    //生成服务会员收款的二维码
    Route::post('recqrcode/get', 'QrcodeController@generateReceipt');
    //获取用户资产详情
    Route::post('member/fund', 'FundController@getMemberFunds');
    //用户账单接口
    Route::post('member/bill', 'FundController@memberBill');
    //服务会员提现接口
    Route::post('smember/withdraw', 'FundController@SMemberwithdraw');
    //服务会员成交列表
    Route::post('member/turnover','FundController@turnover');
    //添加商品到购物车
    Route::post('add/cart', 'CartController@addCart');
    //编辑购物车
    Route::post('edit/cart', 'CartController@editCart');
    //删除购物车商品
    Route::post('delete/cart', 'CartController@deleteCart');
    //清除失效宝贝
    Route::post('purge/expired_goods', 'CartController@purgeExpiredGoods');
    //购物车列表
    Route::post('cart/list', 'CartController@CartList');
    //订单提交
    Route::post('order/commit', 'OrderController@toOrderCommit');
    //订单物流
    Route::post('order/express', 'OrderController@getExpress');
    //订单列表
    Route::post('order/list', 'OrderController@toOrderList');
    //收银台结算
    Route::post('order/pay', 'OrderController@toOrderPay');
    //从订单列表里未支付订单付款
    Route::post('order/listSinglePay', 'OrderController@toOrderSinglePay');
    //确认收货
    Route::post('order/receipt', 'OrderController@confirmReceipt');
    //取消订单
    Route::post('order/cancel', 'OrderController@cancelOrder');
    //删除订单
    Route::post('order/delete', 'OrderController@deleteOrder');
    //代付款和待收货
    Route::post('order/status', 'OrderController@orderStatus');
    //转账
    Route::post('order/transfer','OrderController@transfer');

    //会员提现申请
    Route::post('member/withdrawal_apply','FundController@memberWithdrawalApply');

    //银行卡--添加
    Route::post('card/add',"CardController@add");
    //银行卡--编辑
    Route::post('card/edit',"CardController@edit");
    //银行卡--删除
    Route::post('card/delete',"CardController@delete");
    //银行卡--列表
    Route::post('card/list',"CardController@listcards");
    //银行卡--最新
    Route::post('card/newest',"CardController@newest");
});

/**
 * 管理后台 路由组
 * @author chensq
 * @time 2017-03-29 15:00
 */

Route::group(['namespace' => 'Admin','prefix'=>'admin'], function() {
    // 控制器在 "App\Http\Controllers\Admin" 命名空间下

    Route::get('/', [
        'as' => 'index', 'uses' => 'IndexController@index'
    ]);

    //批处理工具
    Route::get('tools', 'ToolsController@index');
    Route::get('verify', 'ToolsController@verify');
    Route::get('merge', 'ToolsController@merge');
   //登录验证
    Route::get('login','LoginController@login');
    Route::post('login','LoginController@login');
    Route::get('quit','LoginController@quit');

    //订单列表
    Route::get('order/index','OrderController@index');
    //订单详情
    Route::get('order/detail','OrderController@detail');
    //发货
    Route::match(['get','post'],'order/confirmsend','OrderController@confirmsend');
    //查看物流
    Route::get('order/express','OrderController@express');
    //活动列表
    Route::get('activity','ActivityController@index')->name('admin.activity.index');
    Route::any('activity/post','ActivityController@post')->name('admin.activity.post');
    Route::any('activity/changestatus','ActivityController@changestatus');
    Route::any('activity/delete','ActivityController@delete');

    Route::get('test', 'TestController@test');
    //管理员管理
    Route::get('lists', 'AdminController@lists');
    Route::post('lists', 'AdminController@lists');
    Route::get('addadmin', 'AdminController@addadmin');
    Route::post('validDo', 'AdminController@validDo');
    Route::post('deladmin', 'AdminController@deladmin');
    Route::post('addadminDo', 'AdminController@addadminDo');
    
    Route::get('role', 'AdminController@role');
    Route::post('role', 'AdminController@role');
    Route::get('addrole', 'AdminController@addrole');
    Route::post('addroleDo', 'AdminController@addroleDo');
    Route::post('delrole', 'AdminController@delrole');
    Route::get('addrolemenu', 'AdminController@addrolemenu');
    Route::post('addrolemenuDo', 'AdminController@addrolemenuDo');
    
    Route::get('permission', 'AdminController@permission');
    Route::post('permission', 'AdminController@permission');
    Route::get('addpermission', 'AdminController@addpermission');
    Route::post('addpermissionDo', 'AdminController@addpermissionDo');
    Route::post('delpermission', 'AdminController@delpermission');
    
    Route::get('sysmenu', 'AdminController@sysmenu');
    Route::get('addsysmenu', 'AdminController@addsysmenu');
    Route::post('addsysmenuDo', 'AdminController@addsysmenuDo');
    Route::post('delsysmenu', 'AdminController@delsysmenu');
    //商品类目列表
    Route::get('category/index','CategoryController@index');
    //获取子类目
    Route::get('category/getchilds','CategoryController@getchilds')->name('admin.category.getchilds');
    //添加/编辑商品类目
    Route::match(['get','post'],'category/post','CategoryController@post')->name('admin.category.post');
    //删除商品类目
    Route::post('category/delete','CategoryController@delete')->name('admin.category.delete');

    //上传图片
    Route::post('util/uploadfile','UtilController@uploadfile')->name('admin.util.uploadfile');
    //商品
    //商品列表
    Route::get('product/index','ProductController@index')->name('admin.product.index');
    //添加/编辑商品
    Route::match(['get','post'],'product/post','ProductController@post')->name('admin.product.post');
    //删除商品
    Route::get('product/delete','ProductController@delete')->name('admin.product.delete');
    //商品上下架管理
    Route::get('product/changestatus','ProductController@changestatus');
    //幻灯片列表
    Route::match(['get','post'],'filmslide/index','FilmslideController@index');
    //添加/编辑幻灯片
    Route::match(['get','post'],'filmslide/post','FilmslideController@post')->name('admin.filmslide.post');
    //删除幻灯片
    Route::post('filmslide/delete','FilmslideController@delete')->name('admin.filmslide.delete');
    //幻灯片状态管理
    Route::get('filmslide/changestatus','FilmslideController@changestatus');

    //供应商
    Route::get('supplier','SupplierController@index');
    Route::post('supplier','SupplierController@index');
    Route::get('supplier/add','SupplierController@add');
    Route::post('supplier/addDo','SupplierController@addDo');
    //代理列表
    Route::get('agentlist','AgentController@index')->name('admin.agent.index');
    //添加代理
    Route::match(['get','post'],'agentpost','AgentController@post')->name('admin.agent.post');
    //代理资金流水
    Route::get('agentlogs','AgentController@captitallog')->name('admin.agent.logs');
    //代理资金流水
    Route::match(['get','post'],'agent/logs','AgentController@captitallogs');
    //删除代理人
    Route::get('agentdelete','AgentController@delete');
    //服务会员相关操作
    Route::match(['get', 'post'], 'smember', 'SmemberController@index');
    Route::match(['get', 'post'], 'smember/paylist', 'SmemberController@payList');
    Route::match(['get', 'post'], 'smember/singlepay', 'SmemberController@singlePayLog')->name('admin.smember.paylog');
    //审核
    Route::get('smember/audit', 'SmemberController@audit')->name('admin.smember.audit');
    Route::post('smember/toAudit', 'SmemberController@toAudit');
    //信息编辑
    Route::get('smember/{smember}/edit', 'SmemberController@edit')->name('admin.smember.edit');
    Route::post('smember/update', 'SmemberController@update');
    Route::delete('smember/destroy', 'SmemberController@destroy')->name('admin.smember.delete');
    Route::delete('smember/bdestroy', 'SmemberController@batchDestroy')->name('admin.smember.batch_delete');

    //代理一级会员列表
    Route::get('agentmember/index','AgentMemberController@index')->name('admin.agentmember.index');
    //代理添加一级会员
    Route::match(['get','post'],'agentmember/post','AgentMemberController@post')->name('admin.agentmember.post');
    //代理删除一级会员
    Route::post('agentmember/delete','AgentMemberController@delete')->name('admin.agentmember.delete');
    //代理结算
    Route::match(['get','post'],'agent/settle','AgentController@settle')->name('admin.agent.settle');
    //财务管理--充值列表
    Route::get('finance/recharge','FinanceController@recharge');
    //财务管理--提现列表
    Route::get('finance/withdraw','FinanceController@withdraw');

    //会员提现申请列表
    Route::match(['get', 'post'], 'finance/member_withdraw', 'MemberController@memberWithdraw');
    //会员提现申请审核
    Route::get('finance/audit', 'MemberController@audit')->name('admin.finance.member_audit');
    Route::post('finance/toAudit', 'MemberController@toAudit');
    Route::match(['get', 'post'], 'member/singlepay', 'MemberController@singlePayLog')->name('admin.member.paylog');

    //财务管理--服务商现列表
    Route::get('finance/swithdraw','FinanceController@smemberwithdraw');
    //财务管理--服务商提现操作
    Route::any('finance/swithdrawdeal','FinanceController@swithdrawdeal')->name('admin.finance.smember_audit');
    //资金记录
    Route::match(['get', 'post'], 'finance/log', 'FinanceController@payLog')->name('admin.smember.log');
    //用户管理
    //用户列表
    Route::get('member', 'MemberController@index');
    Route::get('member/show', 'MemberController@show')->name('admin.member.show');
    //编辑用户
    Route::get('member/edit', 'MemberController@edit')->name('admin.member.edit');
    Route::post('member/editResult', 'MemberController@editResult');
    //修改密码
    Route::get('member/password', 'MemberController@password')->name('admin.member.password');
    Route::post('member/passwordResult', 'MemberController@passwordResult');
    //删除用户
    Route::post('member/delete', 'MemberController@delete')->name('admin.member.delete');

    //删除的用户列表
    Route::get('member/dellist', 'MemberController@dellist');
    Route::post('member/restore', 'MemberController@restore')->name('admin.member.restore');

    //用户充值
    Route::get('member/recharge', 'MemberController@recharge')->name('admin.member.recharge');
    Route::post('member/rechargeResult', 'MemberController@rechargeResult');

    //后台配置文件管理
    //充值额度配置
    Route::get('config/chargeAmount', 'ConfigController@chargeAmount')->name('admin.config.chargeAmount');
    Route::get('config/amountAdd', 'ConfigController@amountAdd')->name('admin.config.amountAdd');
    Route::post('config/amountPut', 'ConfigController@amountPut')->name('admin.config.amountPut');
    Route::delete('config/amountDel', 'ConfigController@amountDel')->name('admin.config.amountDel');
});
