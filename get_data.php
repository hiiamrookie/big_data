<?php
include(dirname(__FILE__) . '/inc/my_session.php');
include(dirname(__FILE__) . '/inc/require_file.php');
include(dirname(__FILE__) . '/inc/model_require.php');
header('Content-type: text/html; charset=utf-8');

$usersession = Session_Util::my_session_get('user');
if ($usersession === NULL) {
	echo getDefault();
	exit;
}

$action = strval(Security_Util::my_get('action'));
switch ($action) {
	case 'getDepositPayment':
		echo getDepositPayment();
		break;
	case 'getContractPayment':
		echo getContractPayment();
		break;
	case 'getCustomInfoSearch':
		echo getCustomInfoSearch();
		break;
	case 'getPersonPaymentGD':
		echo getPersonPaymentGD();
		break;
	case 'getPersonDepositPaymentGD':
		echo getPersonDepositPaymentGD();
		break;
	case 'getMediaPaymentGD':
		echo getMediaPaymentGD();
		break;
	case 'getMediaDepositPaymentGD':
		echo getMediaDepositPaymentGD();
		break;
	case 'getHedgeReceive':
	case 'getHedgePay':
		echo getHedge($action);
		break;
	case 'getHedgeReceiveByPids':
	case 'getHedgePayByPids':
		echo getHedgeByPids($action);
		break;
	case 'getPayFirstByPaymentApply':
		echo getPayFirstByPaymentApply();
		break;
	case 'getPidFinanceInfo':
		echo getPidFinanceInfo();
		break;
	case 'getCustomPidInfoSearch':
		echo getCustomPidInfoSearch();
		break;
	case 'getPaidPidFinanceInfo':
		echo getPaidPidFinanceInfo();
		break;
	case 'getDepositDeduction':
		echo getDepositDeduction();
		break;
	case 'getPaidDepositInfo':
		echo getPaidDepositInfo();
		break;
	case 'getPidInfo':
		echo getPidInfo();
		break;
	case 'getReceiveInvoiceSource':
		echo getReceiveInvoiceSource();
		break;
	case 'getPaymentApply':
		echo getPaymentApply();
		break;
		//case 'searchPidPayment':
		//	echo searchPidPayment();
		//	break;
	case 'getPaymentRebateItems':
		echo getPaymentRebateItems();
		break;
	case 'getMediaPaymentItems':
		echo getMediaPaymentItems();
		break;
	case 'getMediaDepositPaymentItems':
		echo getMediaDepositPaymentItems();
		break;
	case 'getMediaPaymentItemsUserAssigned':
		echo getMediaPaymentItemsUserAssigned();
		break;
	case 'getMediaDepositPaymentItemsUserAssigned':
		echo getMediaDepositPaymentItemsUserAssigned();
		break;
	case 'getMediaPaymentAssignedPid':
		echo getMediaPaymentAssignedPid();
		break;
	case 'getMediaDepositPaymentAssignedPid':
		echo getMediaDepositPaymentAssignedPid();
		break;
	case 'getMediaPaymentUserAssignedPid':
		echo getMediaPaymentUserAssignedPid();
		break;
	case 'getMediaDepositPaymentUserAssignedPid':
		echo getMediaDepositPaymentUserAssignedPid();
		break;
	case 'getMediaPaymentAssignInfos':
		echo getMediaPaymentAssignInfos();
		break;
	case 'getMediaDepositPaymentAssignInfos':
		echo getMediaDepositPaymentAssignInfos();
		break;
	case 'getSumRebateInvoiceNoCollection':
		echo getSumRebateInvoiceNoCollection();
		break;
	case 'getRebateInvoiceNoCollectionByInvoiceID':
		echo getRebateInvoiceNoCollectionByInvoiceID();
		break;
	case 'searchRebateInvoiceApplyPid':
		echo searchRebateInvoiceApplyPid();
		break;
	case 'getRebateInvoiceGDInfo':
		echo getRebateInvoiceGDInfo();
		break;
	case 'getPaymentApplyInRebateTransfer':
		echo getPaymentApplyInRebateTransfer();
		break;
	case 'getPaymentListInRebateTransfer':
		echo getPaymentListInRebateTransfer();
		break;
	case 'getRebateQuery':
		echo getRebateQuery();
		break;
	case 'getCustomerContractPaymentNimpayfirst':
		echo getCustomerContractPaymentNimpayfirst();
		break;
	case 'getCustomerDepositPaymentNimpayfirst':
		echo getCustomerDepositPaymentNimpayfirst();
		break;
	case 'getDepartNimpayfirst':
		echo getDepartNimpayfirst();
		break;
	case 'getPayFirstByDepartment':
		echo getPayFirstByDepartment();
		break;
	case 'getExecutiveCYByID':
		echo getExecutiveCYByID();
		break;
		//case 'selectPaymentList':
		//	echo selectPaymentList();
		//	break;
	case 'getOutsourcing':
		echo getOutsourcing();
		break;
	default:
		echo getDefault();
}

function getDefault() {
	return json_encode(array());
}

//查询已付保证金列表
function getDepositPayment() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array(
							'searchmedianame' => Security_Util::my_get(
									'searchmedianame'),
							'searchcusname' => Security_Util::my_get(
									'searchcusname'),
							'searchcid' => Security_Util::my_get('searchcid'))));
			return $easyui->getDepositPayment();
}

//查询已付合同款列表
function getContractPayment() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array(
							'searchpaymentdate' => Security_Util::my_get(
									'searchpaymentdate'),
							'searchmedianame' => Security_Util::my_get(
									'searchmedianame'))));
			return $easyui->getContractPayment();
}

//查询付款申请关联的保证金抵扣
function getDepositDeduction() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'),
							'deposit_deductiuon' => Security_Util::my_get(
									'deposit_deductiuon'),
							'payment_type' => Security_Util::my_get(
									'payment_type'),
							'isshow' => Security_Util::my_get('isshow'))));
			return $easyui->getDepositDeduction();
}

function getCustomInfoSearch() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array(
							'medianame' => Security_Util::my_get('medianame'),
							'cusname' => Security_Util::my_get('cusname'),
							'pid' => Security_Util::my_get('pid'),
							'projectname' => Security_Util::my_get(
									'projectname'))));
			return $easyui->getCustomInfoSearch();
}

function getPersonPaymentGD() {
	return getPaymentGD();
}

function getPersonDepositPaymentGD() {
	return getDepositPaymentGD();
}

function getMediaPaymentGD() {
	return getPaymentGD(FALSE);
}

function getMediaDepositPaymentGD() {
	return getDepositPaymentGD(FALSE);
}

function getDepositPaymentGD($isPersonApply = TRUE) {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'sort' => Security_Util::my_post('sort'),
					'order' => Security_Util::my_post('order'),
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getDepositPaymentGD($isPersonApply);
}

function getPaymentGD($isPersonApply = TRUE) {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'sort' => Security_Util::my_post('sort'),
					'order' => Security_Util::my_post('order'),
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getPaymentGD($isPersonApply);
}

function getHedge($action) {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'sort' => Security_Util::my_post('sort'),
					'order' => Security_Util::my_post('order'),
					'qs' => array(
							'medianame' => Security_Util::my_get('medianame'),
							'cusname' => Security_Util::my_get('cusname'),
							'pid' => Security_Util::my_get('pid'),
							'projectname' => Security_Util::my_get(
									'projectname'), 'search_action' => $action)));
			return $easyui->getHedgeSearch();

}

function getHedgeByPids($action) {
	$pids = explode(',', Security_Util::my_get('pids'));
	$p = array();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$p[] = $pid;
		}
	}
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array('search_action' => $action, 'pids' => $p,
							'id' => Security_Util::my_get('id'))));
			return $easyui->getHedgeByPids();

}

function getPayFirstByPaymentApply() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'),
							'stype' => Security_Util::my_get('stype'))));
			return $easyui->getPayFirstByPaymentApply();
}

function getPidFinanceInfo() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array(
							'medianame' => Security_Util::my_get('medianame'),
							'cusname' => Security_Util::my_get('cusname'),
							'pid' => Security_Util::my_get('pid'),
							'projectname' => Security_Util::my_get(
									'projectname'))));
			return $easyui->getPidFinanceInfo();
}

function getCustomPidInfoSearch() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array('cusname' => Security_Util::my_get('cusname'))));
			return $easyui->getCustomPidInfoSearch();
}

//已付款执行单信息
function getPaidPidFinanceInfo() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'sort' => Security_Util::my_post('sort'),
					'order' => Security_Util::my_post('order'),
					'qs' => array(
							'contrct_number' => Security_Util::my_get(
									'contrct_number'),
							'pid' => Security_Util::my_get('pid'),
							'cusname' => Security_Util::my_get('cusname'),
							'medianame' => Security_Util::my_get('medianame'))));
			return $easyui->getPaidPidFinanceInfo();
}

function getPaidDepositInfo() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array('cid' => Security_Util::my_get('cid'),
							'cusname' => Security_Util::my_get('cusname'),
							'medianame' => Security_Util::my_get('medianame'))));
			return $easyui->getPaidDepositInfo();
}

function getPidInfo() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array('pid' => Security_Util::my_get('pid'),
							'cid' => Security_Util::my_get('cid'),
							'cusname' => Security_Util::my_get('cusname'),
							'medianame' => Security_Util::my_get('medianame'))));
			return $easyui->getPidInfo();
}

function getReceiveInvoiceSource() {
	$easyui = new Easyui_Datagrid(
			array('qs' => array('ids' => Security_Util::my_get('ids'))));
	return $easyui->getReceiveInvoiceSource();
}

/*
 function searchPidPayment(){
 $easyui = new Easyui_Datagrid(
 array('page' => Security_Util::my_post('page'),
 'rows' => Security_Util::my_post('rows'),
 'qs' => array('cusname_number' => Security_Util::my_get('cusname_number'),
 'pid' => Security_Util::my_get('pid'),
 'cusname' => Security_Util::my_get('cusname'),
 'medianame' => Security_Util::my_get('medianame'))));
 return $easyui->searchPidPayment();
 }
 */

function getPaymentApply() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array('type' => Security_Util::my_get('type'),
							'payment_date' => Security_Util::my_get(
									'payment_date'),
							'medianame' => Security_Util::my_get('medianame'))));
			return $easyui->getPaymentApply();
}

function getPaymentRebateItems() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getPaymentRebateItems();
}

function getMediaPaymentItems() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getMediaPaymentItems();
}

function getMediaDepositPaymentItems() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getMediaDepositPaymentItems();
}

function getMediaPaymentItemsUserAssigned() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'),
							'uid' => Security_Util::my_get('uid'))));
			return $easyui->getMediaPaymentItemsUserAssigned();
}

function getMediaDepositPaymentItemsUserAssigned() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'),
							'uid' => Security_Util::my_get('uid'))));
			return $easyui->getMediaDepositPaymentItemsUserAssigned();
}

function getMediaPaymentAssignedPid() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getMediaPaymentAssignedPid();
}

function getMediaDepositPaymentAssignedPid() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getMediaDepositPaymentAssignedPid();
}

function getMediaPaymentUserAssignedPid() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'),
							'uid' => Security_Util::my_get('uid'))));
			return $easyui->getMediaPaymentUserAssignedPid();
}

function getMediaDepositPaymentUserAssignedPid() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'),
							'uid' => Security_Util::my_get('uid'))));
			return $easyui->getMediaDepositPaymentUserAssignedPid();
}

//获取批量合同款申请员工分配情况
function getMediaPaymentAssignInfos() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getMediaPaymentAssignInfos();
}

function getMediaDepositPaymentAssignInfos() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'apply_id' => Security_Util::my_get('apply_id'))));
			return $easyui->getMediaDepositPaymentAssignInfos();
}

function getSumRebateInvoiceNoCollection() {
	$easyui = new Easyui_Datagrid();
	return $easyui->getSumRebateInvoiceNoCollection();
}

function getRebateInvoiceNoCollectionByInvoiceID() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'invoice_id' => Security_Util::my_get('invoice_id'))));
			return $easyui->getRebateInvoiceNoCollectionByInvoiceID();
}

function searchRebateInvoiceApplyPid() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array('type' => Security_Util::my_get('type'),
							'pid' => Security_Util::my_get('pid'),
							'cusname' => Security_Util::my_get('cusname'),
							'medianame' => Security_Util::my_get('medianame'),
							'starttime' => Security_Util::my_get('starttime'),
							'endtime' => Security_Util::my_get('endtime'))));
			return $easyui->searchRebateInvoiceApplyPid();
}

function getRebateInvoiceGDInfo() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array('type' => Security_Util::my_get('type'),
							'id' => Security_Util::my_get('id'))));
			return $easyui->getRebateInvoiceGDInfo();
}

function getPaymentApplyInRebateTransfer() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array(
							'searchpid' => Security_Util::my_get('searchpid'),
							'searchcusname' => Security_Util::my_get(
									'searchcusname'),
							'searchmedianame' => Security_Util::my_get(
									'searchmedianame'),
							'searchpaydate' => Security_Util::my_get(
									'searchpaydate'),
							'searchpayplan' => Security_Util::my_get(
									'searchpayplan'),
							'searchpayreal' => Security_Util::my_get(
									'searchpayreal'))));
			return $easyui->getPaymentApplyInRebateTransfer();
}

function getPaymentListInRebateTransfer() {
	$easyui = new Easyui_Datagrid(
			array('qs' => array('itemid' => Security_Util::my_get('itemid'))));
	return $easyui->getPaymentListInRebateTransfer();
}

function getRebateQuery() {
	$easyui = new Easyui_Datagrid(
			array('page' => Security_Util::my_post('page'),
					'rows' => Security_Util::my_post('rows'),
					'qs' => array(
							'medianame' => Security_Util::my_get('medianame'),
							'cusname' => Security_Util::my_get('cusname'),
							'depname' => Security_Util::my_get('depname'),
							'startdate' => Security_Util::my_get('startdate'),
							'enddate' => Security_Util::my_get('enddate'))));
			return $easyui->getRebateQuery();
}

function getCustomerContractPaymentNimpayfirst() {
	$easyui = new Easyui_Datagrid(
			array('qs' => array('cusname' => Security_Util::my_get('cusname'))));
	return $easyui->getCustomerContractPaymentNimpayfirst();
}

function getCustomerDepositPaymentNimpayfirst() {
	$easyui = new Easyui_Datagrid(
			array('qs' => array('cusname' => Security_Util::my_get('cusname'))));
	return $easyui->getCustomerDepositPaymentNimpayfirst();
}

function getDepartNimpayfirst() {
	$easyui = new Easyui_Datagrid();
	return $easyui->getDepartNimpayfirst();
}

function getPayFirstByDepartment() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array('city' => Security_Util::my_get('city'),
							'dep' => Security_Util::my_get('dep'),
							'team' => Security_Util::my_get('team'))));
			return $easyui->getPayFirstByDepartment();
}

function getExecutiveCYByID() {
	$easyui = new Easyui_Datagrid(
			array(
					'qs' => array(
							'executive_id' => Security_Util::my_get(
									'executive_id'),
							'dep' => Security_Util::my_get('dep'))));
			return $easyui->getExecutiveCYByID();
}

/*
 function selectPaymentList() {
 $easyui = new Easyui_Datagrid(
 array(
 'qs' => array(
 'apply' => Security_Util::my_get('apply'))));
 return $easyui->selectPaymentList();
 }
 */

function getOutsourcing() {
	$easyui = new Easyui_Datagrid(
			array('qs' => array('id' => Security_Util::my_get('id'))));
	return $easyui->getOutsourcingByID();
}
