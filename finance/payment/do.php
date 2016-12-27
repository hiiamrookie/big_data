<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

//校验vcode
$vcode = $uid . User::SALT_VALUE;
$is_ajax = TRUE;
include(dirname(dirname(dirname(__FILE__))) . '/validate_vcode.php');

switch (strval(Security_Util::my_post('action'))) {
case 'search_executive':
	echo search_executive();
	break;
case 'search_simple_contract':
	echo search_simple_contract();
	break;
case 'search_media_bank':
	echo search_media_bank();
	break;
case 'search_media_bank_account':
	echo search_media_bank_account();
	break;
case 'cancel':
	echo cancel_payment();
	break;
case 'cancelDeposit':
	echo cancelDeposit();
	break;
case 'setdefaultbank':
	echo setdefaultbank();
	break;
case 'setonline':
	echo setonline();
	break;
case 'getuser':
	echo getuser();
	break;
case 'auditem':
	echo auditem();
	break;
case 'auditMediaPaymentItem':
	echo auditMediaPaymentItem();
	break;
case 'auditMediaDepositPaymentItem':
	echo auditMediaDepositPaymentItem();
	break;
case 'auditemDeposit':
	echo auditemDeposit();
	break;
case 'search_payfirst':
	echo search_payfirst();
	break;
case 'search_contract':
	echo search_contract();
	break;
case 'search_pid_payment':
	echo search_pid_payment();
	break;
case 'editPPNimPayFirst':
	echo editPPNimPayFirst();
	break;
default:
	echo INVALIDATION_VISIT;
}

function search_executive() {
	$payment = new Payment_Person_Apply(
			array(
					'page' => intval(Security_Util::my_post('page')) === 0 ? 1
							: intval(Security_Util::my_post('page')),
					'search' => Security_Util::my_post('search'),
					'cusname' => Security_Util::my_post('cusname'),
					'projectname' => Security_Util::my_post('projectname'),
					'medianame' => Security_Util::my_post('medianame')));
	return $payment->get_search_executive_html();
}

function search_simple_contract(){
	$payment = new Payment_Media_Deposit_Apply(
			array(
					'page' => intval(Security_Util::my_post('page')) === 0 ? 1
							: intval(Security_Util::my_post('page')),
					'cid' => Security_Util::my_post('cid'),
					'cusname' => Security_Util::my_post('cusname')));
	return $payment->get_search_contract_html();
}

function search_media_bank() {
	return Payment_Media_Info::get_bank_list(
			Security_Util::my_post('media_name'));
}

function search_media_bank_account() {
	return Payment_Media_Info::get_bank_acount_list(
			Security_Util::my_post('media_name'),
			Security_Util::my_post('bank_name'));
}

function cancel_payment() {
	$payment = new Payment_Person_Apply(
			array('type' => Security_Util::my_post('type'),
					'id' => Security_Util::my_post('id')));
	return $payment->cancel_payment_apply();
}

function cancelDeposit(){
	$payment = new Payment_Person_Apply_Deposit(
			array('type' => Security_Util::my_post('type'),
					'id' => Security_Util::my_post('id')));
	return $payment->cancel_deposit_payment_apply();
}

function setdefaultbank() {
	$bank = new Nim_BankInfo(
			array('id' => Security_Util::my_post('id'),
					'is_default' => Security_Util::my_post('isdefault')));
	return $bank->set_default_bank();
}

function setonline() {
	$bank = new Nim_BankInfo(
			array('id' => Security_Util::my_post('id'),
					'status' => Security_Util::my_post('status')));
	return $bank->set_bank_online();
}

function getuser() {
	$q = Security_Util::my_post('q');
	$dao = new Dao_Impl();
	//$s = array();
	$results = $dao->db
			->get_results(
					'SELECT uid,username,realname FROM users WHERE islive=1 AND (username LIKE "%'
							. $q . '%" OR realname LIKE "%' . $q . '%")');
	$s = '';
	if ($results !== NULL) {
		$s .= '<table width="100%">';
		foreach ($results as $key => $result) {
			$s .= ($key === 0 || $key % 5 === 0 && $key >= 5 ? '<tr>' : '')
					. '<td id="' . $key
					. '"><input type="checkbox" name="checkuser[]" value="'
					. $result->uid . '">&nbsp;<span id="usershow_'
					. $result->uid . '">' . $result->realname . ' ('
					. $result->username . ')</span></td>'
					. ($key % 5 === 4 ? '</tr>' : '');
		}
		$s .= '</table>';
	}
	return $s;
}

function auditMediaPaymentItem(){
	$payment = new Payment_Media_Apply(array('id' => Security_Util::my_post('apply_id'),
					'listid' => Security_Util::my_post('listid'),
					'auditsel' => Security_Util::my_post('auditsel'),
					'auditresaon' => Security_Util::my_post('auditresaon')));
	$result = $payment->audit_item();
	return $result['message'];
}

function auditMediaDepositPaymentItem(){
	$payment = new Payment_Media_Deposit_Apply(array('id' => Security_Util::my_post('apply_id'),
					'listid' => Security_Util::my_post('listid'),
					'auditsel' => Security_Util::my_post('auditsel'),
					'auditresaon' => Security_Util::my_post('auditresaon')));
	$result = $payment->audit_item();
	return $result['message'];
}

function auditem() {
	$payment = new Payment_Person_Apply(
			array('id' => Security_Util::my_post('apply_id'),
					'listid' => Security_Util::my_post('listid'),
					'auditsel' => Security_Util::my_post('auditsel'),
					'auditresaon' => Security_Util::my_post('auditresaon')));
	$result = $payment->audit_item();
	if($result['status']==='success'){
		return 1;
	}	
	return $result['message'];
}

function editPPNimPayFirst(){
	$payment = new Payment_Person_Apply(
			array('id' => Security_Util::my_post('apply_id'),
					'listid' => Security_Util::my_post('listid'),
					'is_nim_pay_first' => Security_Util::my_post('ischecked'),
					'nim_pay_first_amount' => Security_Util::my_post('amount')));
	$result = $payment->editPPNimPayFirst();
	if($result['status']==='success'){
		return 1;
	}	
	return $result['message'];
}

function auditemDeposit(){
	$payment = new Payment_Person_Apply_Deposit(
			array('id' => Security_Util::my_post('apply_id'),
					'listid' => Security_Util::my_post('listid'),
					'auditsel' => Security_Util::my_post('auditsel'),
					'auditresaon' => Security_Util::my_post('auditresaon')));
	$result = $payment->audit_item();
	if($result['status']==='success'){
		return 1;
	}	
	return $result['message'];
}

function search_payfirst() {
	$type = Security_Util::my_post('type');
	if ($type === 'customer') {
		$fields = array('custom_name' => Security_Util::my_post('custom_name'),
				'starttime' => Security_Util::my_post('starttime'),
				'endtime' => Security_Util::my_post('endtime'));
	}else if($type === 'apply'){
		$fields = array('paymentype' => Security_Util::my_post('paymentype'),
				'media_name' => Security_Util::my_post('media_name'),
				'paytime' => Security_Util::my_post('paytime'),'payamount'=>Security_Util::my_post('payamount'));
	}
	$fields['type'] = $type;
	$nimpayfirst = new Payment_Nimpayfirst($fields);
	return $nimpayfirst->search_payfirst();
}

function search_contract() {
	$payment_apply_deposit = new Payment_Person_Apply_Deposit(
			array('cid' => Security_Util::my_post('cid'),
					'cusname' => Security_Util::my_post('cusname'),'page'=>Security_Util::my_post('page')));
	return $payment_apply_deposit->search_contract();
}

function search_pid_payment(){
$payment = new Payment_Person_Apply(
			array(
					'page' => intval(Security_Util::my_post('page')) === 0 ? 1
							: intval(Security_Util::my_post('page')),
					'cusname_number' => Security_Util::my_post('cusname_number'),
					'cusname' => Security_Util::my_post('cusname'),
					'pid' => Security_Util::my_post('projectname'),
					'medianame' => Security_Util::my_post('medianame')));
	return $payment->get_search_pid_payment_html();
}