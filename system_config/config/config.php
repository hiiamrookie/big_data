<?php
//网站base url
/**
 * 本机测试
 */
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
define('BASE_URL', 'http://testall.nimads.com/big_data/');

/**
 * 测试服务器
 */
//define ( 'BASE_URL', 'http://oatest.nimads.com/' );

/**
 * 正式服务器
 */
//define ( 'BASE_URL', 'http://oa.nimads.com/' );

//数据库
/**
 * 本机测试
 */
/*define('DB_USER', 'root');
define('DB_PASSWORD', 'thizlinux310917');
///define('DB_NAME', 'oa_local');
define('DB_NAME', 'oa_dabiao');
define('DB_HOST', 'localhost');*/

/**
 * 测试服务器
 */
define ( 'DB_USER', 'root' );
define ( 'DB_PASSWORD', '' );
define ( 'DB_NAME', 'big_data' );
define ( 'DB_HOST', 'localhost' );

/**
 *	正式服务器
 */

//define ( 'DB_USER', 'root' );
//define ( 'DB_PASSWORD', '' );
//define ( 'DB_NAME', 'oa_new' );
//define ( 'DB_HOST', 'localhost' );

//是否启用保险额度检测
define('CUSTOMER_SAFETY_ON', FALSE);

//是否启用成本拆月
define('CY_ON', TRUE);

//是否启用供应商
define('NEW_SUPPLIER_ON', TRUE);

//缓存文件夹
define('CACHE_FOLDER', 'cache/');

//缓存目录
define('CACHE_PATH', dirname(dirname(dirname(__FILE__))) . '/' . CACHE_FOLDER);

//cache时间
define('CACHE_TIME', 3600);

//执行单模块
define('EXECUTIVE_MODULE', 'sys1');

//执行单项目负责人
define('EXECUTIVE_IN_CHARGE', 'sys5');

//部门支持
define('DEP_SUPPORT', 'DEP');

//执行单管理者用户名
$executive_manager_array = array('vicky.cui', 'jesse', 'miranda.wang','yoga.mei','yuan.zhou','betty');

//可新建合同的权限
$add_contract_permission = array('sys44');

//可管理合同的权限
$manager_contract_permission = array('sys44', 'dep55', 'dep61', 'dep111',
		'dep112', 'dep113');

//查阅合同权限
$check_contract_permission = array('vicky.cui', 'jesse', 'miranda.wang','yoga.mei','yuan.zhou','betty');

//撤销合同
$cancel_contract_permission = array('susan.x');

//可管理财务的权限
$manager_finance_permission = array('jesse','yuan.zhou','betty');

//可发起媒体付款申请的权限
$finance_media_payment_permission = array('evon','jesse','yuan.zhou','betty');

//付款申请初审权限
$finance_payment_check_permission = array('linda');

//可管理财务统计的权限
$manager_finance_tj_permission = array('jesse','yuan.zhou','betty');

//可管理媒体数据的权限
$manager_media_data_permission = array('jesse', 'echo','yuan.zhou','betty');

//可管理系统参数的权限
$manager_setup_permission = array('jesse', 'vicky.cui', 'miranda.wang','yoga.mei','yuan.zhou','betty');

//可管理系统客户及保险额度信息的权限
$manager_setup_customer_safety_permission = array('alex.hu', 'jesse','yuan.zhou','betty');

//可查看执行单财务拆月数据的权限
$view_executive_finance_permission = array('vicky.cui', 'jesse', 'miranda.wang','yoga.mei','yuan.zhou','betty');

//供应商申请审核人
$supplier_apply_check_permission = array('jesse', 'linda','miranda.wang','yuan.zhou','betty');

//个人申请退客户款审核人
$person_refund_apply_audit_permission = array('jesse','linda','alex.hu','yuan.zhou','betty');

//收付对冲审核人
$finanace_hedge_permission = array('jesse','linda','alex.hu','yuan.zhou','betty');

//执行单管理菜单
$user_left_executive = array('executive^add' => '新建执行单',
		'executive^mylist' => '我的执行单', 'executive^alllist' => '执行单查阅',
		'executive^manage' => '执行单管理');

//合同管理
$user_left_contract = array('contract_cus^add' => '新建客户合同',
		'contract_cus^mylist' => '我的客户合同', 'contract_cus^list' => '客户合同查阅',
		'contract_cus^manage' => '客户合同管理');

//财务管理
$user_left_finance = array(//'finance^payment^apply' => '付款申请',
		//'finance^payment^person_apply_manager' => '付款申请管理',
		'finance^invoice^apply' => '开票申请', //'finance^deposit^apply' => '保证金申请',
		//'finance^deposit^deposit_invoicelist' => '保证金管理',
		//'finance^payment^paymentlist' => '付款管理',
		//'finance^payment^nimpayfirst' => '垫付管理',
		//'finance^payment^pidedit' => '修改转移',
		//'finance^payment^pidtransfer' => '修改转移',
		//'finance^receiveinvoice^receiveinvoicelist' => '收票管理',
		//'finance^receivables^receivableslist' => '收款管理',
		'finance^invoice^invoicelist' => '开票管理', //	'finance^admin'=>'财务审核',
		'finance^tjall' => '统计分析',
		//'finance^receivables^receivables_normal_search' => '收款查询',
		//'finance^payment^media_apply' => '媒体批量付款申请',
		//'finance^payment^media_manager' => '媒体批量付款管理',
		//'finance^payment^media_gd' => '媒体付款归档',
		'finance^supplier^supplierlist' => '供应商管理',
		'finance^supplier^apply' => '供应商申请', 
		'finance^report' => '财务大表',
		//'finance^custominfosearch'=>'财务信息查询',
		//'finance^hedge'=>'收付对冲',
		'finance^nim_bankinfo' => '银行信息管理',
		//'finance^refund^apply'=>'退款申请',
		//'finance^refund^manager'=>'退款管理',
		//'finance^rebate^manager'=>'返点管理',
		//'finance^rebate^apply_invoice'=>'返点开票申请',
		'finance^settle_account'=>'结帐日期设置',
		'finance^meida_short'=>'媒体简称设置',
		'finance^setting_rebate'=>'返点比例设置'
);

//数据报表
$user_left_report = array('report^data' => '数据报表');

//媒体数据管理
$user_left_media_data = array('upload^uploadfile' => '排期上传测试',
		'media^medialibrary^mtlist' => '媒体库管理',
		'upload^uploadfile1' => '外包成本上传测试');

//个人信息管理
$user_left_own = array('hr^changepwd' => '密码修改');

//系统设置
$user_left_setup = array('hr^userlist' => '账户编辑', 'hr^companylist' => '公司编辑',
		'hr^departmentlist' => '部门编辑', 'hr^teamlist' => '团队编辑',
		'system^permissionlist' => '模块角色权限编辑',
		'system^deppermissionlist' => '部门角色权限编辑',
		'manage^processlist' => '流程编辑', 'manage^depprocesslist' => '部门內部流程编辑',
		'manage^customerlist' => '系统客户编辑');

//外包流程管理
//$outsourcing_process = array('outsourcing^addtype'=>'新建执行单外包类型','outsourcing^addprocess'=>'新建执行单外包审核流程','outsourcing^auditoutsourcinglists'=>'审核执行单外包列表');

//外包流程可管理人员
$manager_outsourcing_process_permission = array('red00','jesse');

//会议室预定
$user_left_booking = array('booking' => '会议室预定（上海）');

//技术部项目管理
$user_left_tect_project = array('tec^projectadd' => '新建项目需求');

//上传文件文件夹
define('UPLOAD_FILE_FOLDER', 'pp');

//上传文件目录
define('UPLOAD_FILE_PATH',
		dirname(dirname(dirname(__FILE__))) . '/' . UPLOAD_FILE_FOLDER);

//上传文件最大5M
define('UPLOAD_FILE_MAX_SIZE', 5 * 1024 * 1024);

//允许上传的excel类型
$defined_upload_execel_validate_type = array('xls', 'xlsx');

//允许上传的excel mime类型
$defined_upload_execel_validate_mime = array('application/excel',
		'application/vnd.ms-excel', 'application/msexcel',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

//上传文件的允许类型
$defined_upload_validate_type = array_merge(
		array('pdf', 'eml', 'msg', 'doc', 'docx', 'jpg', 'png', 'gif'),
		$defined_upload_execel_validate_type);

//上传文件的允许mime类型
$defined_upload_validate_mime = array_merge(
		array('application/pdf', 'text/plain', 'application/octet-stream',
				'message/rfc822', 'application/vnd.ms-outlook', 'image/gif',
				'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png',
				'application/msword',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
		$defined_upload_execel_validate_mime);

//模板总文件夹
define('TEMPLATE_FOLDER', 'template/');

//模板总目录
define('TEMPLATE_PATH',
		dirname(dirname(dirname(__FILE__))) . '/' . TEMPLATE_FOLDER);

//服务费用类型
$defined_fw_amount_type = array('1' => '策略费用', '2' => '创意费用', '3' => 'EPR费用',
		'4' => 'SEO费用', '5' => 'SEM费用', '6' => '制作费用', '7' => 'IDC费用',
		'8' => '技术开发费用', '9' => '系统检测费用', '10' => '媒介服务费用', '11' => '其他费用');

//服务金额拆分类型
$defined_servicecf_type = array('1' => '策略费用', '2' => '创意/制作费用',
		'3' => 'SMC(Social)费用', '4' => 'SEO费用', '5' => 'SEM费用', '7' => 'IDC费用',
		'8' => '技术开发费用', '9' => '系统监测费用', '10' => '媒介服务费用', '11' => '税金费用',
		'12' => '其他费用', '13' => '客户服务费用', '14' => '公关费用','15'=>'媒介费用');

//广告金额拆分类型
$defined_ggcf_type = array(0=>'无');

//执行单成本类型
$defined_executive_cost_type = array('1' => '媒介成本', '2' => '硬件成本',
		'3' => '搜索成本', '4' => '效果成本', '6' => '媒体公关成本（个人）', '7' => '客户返点',
		'8' => '外包成本（公司）', '9' => '媒体公关成本（公司）');

//合同客户类型
$defined_contract_customer_type = array('1' => 'A-1类', '2' => 'A-2类',
		'3' => 'B类', '5' => 'B-1类', '6' => 'B-2类', '4' => 'C类');

//错误信息
define('NO_RIGHT_TO_DO_THIS', '你没有权限操作该项目');

define('INVALIDATION_VISIT', '非正常访问');

define('DOUBLE_POST_ALERT', '请不要重复提交数据');

//时区
//if (date_default_timezone_get() !== 'PRC') {
	date_default_timezone_set('PRC');
//}

//718 add
	
//服务合同税点
define('FW_TAX_RATE',0.0683);
//广告合同税点
define('GG_TAX_RATE',0.1037);
