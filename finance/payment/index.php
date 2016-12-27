<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'apply':
	person_apply();
	break;
case 'media_apply':
	media_apply();
	break;
case 'media_deposit_apply':
	media_deposit_apply();
	break;
case 'paymentlist':
	person_payment_list();
	break;
case 'media_apply_user_assignlist':
	media_apply_user_assignlist();
	break;
case 'media_apply_deposit_user_assignlist':
	media_apply_deposit_user_assignlist();
	break;
case 'payment_userinput':
	payment_userinput();
	break;
case 'payment_deposit_userinput':
	payment_deposit_userinput();
	break;
case 'payment_apply_mylist':
	payment_apply_mylist();
	break;
case 'payment_apply_deposit_mylist':
	payment_apply_deposit_mylist();
	break;
case 'get_media_info':
	get_media_info();
	break;
case 'continue_payment_apply':
case 'edit_payment_apply':
	edit_payment_apply(Security_Util::my_get('o'));
	break;
case 'edit_payment_deposit_apply':
	edit_payment_deposit_apply();
	break;
case 'nimpayfirst':
	nimpayfirst();
	break;
case 'nimpayfirst_list':
	nimpayfirst_list();
	break;
case 'pidedit':
	pidedit();
	break;
case 'pidtransfer':
	pidtransfer();
	break;
case 'mymedialist':
	mymedialist();
	break;
case 'my_media_deposit_apply_list':
	my_media_deposit_apply_list();
	break;
case 'editmymediapayment':
	editmymediapayment();
	break;
case 'editmymediadepositpayment':
	editmymediadepositpayment();
	break;
case 'media_manager':
	media_manager();
	break;
case 'media_deposit_manager':
	media_deposit_manager();
	break;
case 'auditmediapayment':
	auditmediapayment();
	break;
case 'audituserassigned':
	audituserassigned();
	break;
case 'audituserdepositassigned':
	audituserdepositassigned();
	break;
case 'auditmediadepositpayment':
	auditmediadepositpayment();
	break;
case 'media_gd':
	media_gd();
	break;
case 'media_deposit_gd':
	media_deposit_gd();
	break;
case 'person_apply_manager':
	person_apply_manager();
	break;
case 'person_deposit_apply_manager':
	person_deposit_apply_manager();
	break;
case 'person_apply_manager_audit':
	person_apply_manager_audit();
	break;
case 'person_deposit_apply_manager_audit':
	person_deposit_apply_manager_audit();
	break;
case 'person_apply_manager_gd':
	person_apply_manager_gd();
	break;
case 'person_deposit_apply_manager_gd':
	person_deposit_apply_manager_gd();
	break;
case 'payment_apply_deposit':
	payment_apply_deposit();
	break;
case 'deposit2deposit':
	deposit2deposit();
	break;
case 'deposit2pid':
	deposit2pid();
	break;
case 'print':
	payment_print();
	break;
case 'my_media_assigned':
	my_media_assigned();
	break;
case 'my_media_deposit_assigned':
	my_media_deposit_assigned();
	break;
case 'getMediaName':
	getMediaName();
	break;
default:
	User::no_done();
}

function person_apply() {
	$payment_person_apply = new Payment_Person_Apply();
	echo $payment_person_apply->get_payment_person_apply_html();
	unset($payment_person_apply);
}

function media_apply() {
	$payment_media_apply = new Payment_Media_Apply();
	echo $payment_media_apply->get_payment_media_apply_html();
	unset($payment_media_apply);
}

function media_deposit_apply() {
	$payment_media_apply = new Payment_Media_Deposit_Apply();
	echo $payment_media_apply->get_payment_media_deposit_apply_html();
	unset($payment_media_apply);
}

function person_payment_list() {
	$person_payment_list = new Payment_Person_List();
	echo $person_payment_list->get_payment_person_list_html();
	unset($person_payment_list);
}

//function media_payment_list() {
//	$media_payment_list = new Payment_Media_List();
//	echo $media_payment_list->get_payment_media_list_html();
//	unset($media_payment_list);
//}
function media_apply_user_assignlist() {
	$user_input = new Payment_Media_Apply(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $user_input->get_payment_media_apply_user_assign_html();
	unset($user_input);
}

function media_apply_deposit_user_assignlist() {
	$user_input = new Payment_Media_Deposit_Apply(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $user_input->get_payment_media_deposit_apply_user_assign_html();
	unset($user_input);
}

function payment_userinput() {
	$user_input = new Payment_Media_Apply_User_Input();
	echo $user_input->get_payment_media_apply_user_input_html();
	unset($user_input);
}

function payment_deposit_userinput() {
	$user_input = new Payment_Media_Deposit_Apply_User_Input();
	echo $user_input->get_payment_media_deposit_apply_user_input_html();
	unset($user_input);
}

function payment_apply_mylist() {
	$mylist = new Payment_Person_Mylist(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $mylist->get_payment_person_mylist_html();
	unset($mylist);
}

function payment_apply_deposit_mylist() {
	$mylist = new Payment_Person_Deposit_Mylist(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $mylist->get_payment_person_deposit_mylist_html();
	unset($mylist);
}

function get_media_info() {
	echo Payment_Media_Info::get_media_list();
}

function edit_payment_apply($o) {
	$payment_person_apply = new Payment_Person_Apply();
	echo $payment_person_apply->get_edit_payment_person_apply_html($o);
	unset($payment_person_apply);
}

function edit_payment_deposit_apply() {
	$payment = new Payment_Person_Apply_Deposit();
	echo $payment->get_edit_payment_deposit_apply_html();
	unset($payment);
}

function nimpayfirst() {
	$nim = new Payment_Nimpayfirst();
	echo $nim->get_nimpayfirst_html();
	unset($nim);
}

function nimpayfirst_list() {
	$nim = new Payment_Nimpayfirst();
	echo $nim->get_nimpayfirst_list_html();
	unset($nim);
}

function pidedit() {
	$editransfer = new Payment_Pid_Edit_Transfer();
	echo $editransfer->get_payment_pid_edit_html();
	unset($editransfer);
}

function pidtransfer() {
	$editransfer = new Payment_Pid_Edit_Transfer();
	echo $editransfer->get_payment_pid_transfer_html();
	unset($editransfer);
}

function mymedialist() {
	$payment_media = new Payment_Media_Apply(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $payment_media->get_payment_media_mylist_html();
	unset($payment_media);
}

function my_media_deposit_apply_list() {
	$payment_media = new Payment_Media_Deposit_Apply(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $payment_media->get_payment_media_mylist_html();
	unset($payment_media);
}

function editmymediapayment() {
	$payment_media_apply = new Payment_Media_Apply();
	echo $payment_media_apply->get_payment_media_edit_html();
	unset($payment_media_apply);
}

function editmymediadepositpayment() {
	$payment_media_apply = new Payment_Media_Deposit_Apply();
	echo $payment_media_apply->get_payment_media_deposit_edit_html();
	unset($payment_media_apply);
}

function media_manager() {
	$payment_media_apply = new Payment_Media_Apply(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $payment_media_apply->get_payment_media_manager_html();
	unset($payment_media_apply);
}

function media_deposit_manager() {
	$payment_media_apply = new Payment_Media_Deposit_Apply(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $payment_media_apply->get_payment_media_deposit_manager_html();
	unset($payment_media_apply);
}

function auditmediapayment() {
	$payment_media_apply = new Payment_Media_Apply();
	echo $payment_media_apply->get_payment_media_audit_html();
	unset($payment_media_apply);
}

//审核员工输入条目
function audituserassigned() {
	$payment_media_apply = new Payment_Media_Apply();
	echo $payment_media_apply->get_payment_media_audit_user_assigned_html();
	unset($payment_media_apply);
}

function audituserdepositassigned() {
	$payment_media_apply = new Payment_Media_Deposit_Apply();
	echo $payment_media_apply
			->get_payment_media_deposit_audit_user_assigned_html();
	unset($payment_media_apply);
}

function auditmediadepositpayment() {
	$payment_media_apply = new Payment_Media_Deposit_Apply();
	echo $payment_media_apply->get_payment_media_deposit_audit_html();
	unset($payment_media_apply);
}

function media_gd() {
	$payment_media_apply = new Payment_Media_Apply();
	echo $payment_media_apply->get_payment_media_gd_html();
	unset($payment_media_apply);
}

function media_deposit_gd() {
	$payment_media_apply = new Payment_Media_Deposit_Apply();
	echo $payment_media_apply->get_payment_media_deposit_gd_html();
	unset($payment_media_apply);
}

function person_apply_manager() {
	$payment_person_apply = new Payment_Person_Apply(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $payment_person_apply->get_payment_person_apply_list_html();
	unset($payment_person_apply);
}

function person_deposit_apply_manager() {
	$payment_person_deposit_apply = new Payment_Person_Apply_Deposit(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $payment_person_deposit_apply
			->get_payment_person_deposit_apply_list_html();
	unset($payment_person_deposit_apply);
}

function person_apply_manager_audit() {
	$payment_person = new Payment_Person_Apply();
	echo $payment_person->get_person_apply_manager_audit_html();
	unset($payment_person);
}

function person_deposit_apply_manager_audit() {
	$payment_person = new Payment_Person_Apply_Deposit();
	echo $payment_person->get_person_deposit_apply_manager_audit_html();
	unset($payment_person);
}

function person_apply_manager_gd() {
	$payment_person = new Payment_Person_Apply();
	echo $payment_person->get_person_apply_manager_gd_html();
	unset($payment_person);
}

function person_deposit_apply_manager_gd() {
	$payment_person = new Payment_Person_Apply_Deposit();
	echo $payment_person->get_person_deposit_apply_manager_gd_html();
	unset($payment_person);
}

function payment_apply_deposit() {
	$payment = new Payment_Person_Apply_Deposit();
	echo $payment->get_payment_person_apply_deposit_html();
	unset($payment);
}

function deposit2deposit() {
	$deposit = new Payment_Deposit_Transfer();
	echo $deposit->getDeposit2DepositHtml();
	unset($deposit);
}

function deposit2pid() {
	$deposit = new Payment_Deposit_Transfer();
	echo $deposit->getDeposit2PidHtml();
	unset($deposit);
}

function payment_print() {
	$objPHPExcel = new PHPExcel();
	$objPHPexcel = PHPExcel_IOFactory::load(
			TEMPLATE_PATH . 'bank_payment_voucher.xls');

	$objPHPexcel->setActiveSheetIndex(0);
	$objWorksheet = $objPHPexcel->getActiveSheet();

	$excel = new Excel(
			array('apply_id' => intval(Security_Util::my_get('id')),
					'type' => Security_Util::my_get('type')));
	$datas = $excel->get_datas('payment');

	if (!empty($datas)) {
		foreach ($datas as $key => $value) {
			//插入基本数据
			$objWorksheet->setCellValueExplicit($key, $value['v'], $value['t']);
		}

		ob_end_clean();
		header(
				'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header(
				'Content-Disposition: attachment;filename="bank_payment_voucher.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPexcel, 'Excel2007');
		$objWriter->save('php://output');
		$objPHPexcel->disconnectWorksheets();
		unset($objPHPexcel);
	} else {
		User::no_object('没有该付款申请单或去权限');
	}
}

//媒体批量付款自己已分配记录
function my_media_assigned() {
	$my = new Payment_Media_Apply_User_Input(
			array('id' => Security_Util::my_get('id')));
	echo $my->getMyMediaAssignedHtml();
	unset($my);
}

//媒体保证金批量付款自己已分配记录
function my_media_deposit_assigned() {
	$my = new Payment_Media_Deposit_Apply_User_Input(
			array('id' => Security_Util::my_get('id')));
	echo $my->getMyMediaDepositAssignedHtml();
	unset($my);
}

//根据媒体名称关键字搜索媒体
function getMediaName(){
	$q = Security_Util::my_get('q');
	$dao = new Dao_Impl();
	$s = '';
	$results = $dao->db
			->get_results(
					'SELECT DISTINCT(media_name) AS media_name FROM finance_payment_media_info WHERE isok=1 AND media_name LIKE "%'
							. $q . '%"');
	if ($results !== NULL) {
		foreach ($results as $result) {
			$s .= $result->media_name  . "\n";
		}
	}
	echo $s;
}