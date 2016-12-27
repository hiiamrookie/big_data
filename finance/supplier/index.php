<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'supplierlist':
	supplierlist();
	break;
case 'supplierimport':
	supplierimport();
	break;
case 'supplieredit':
	supplieredit();
	break;
case 'apply':
	supplierapply();
	break;
case 'mylist':
	mylist();
	break;
case 'view_myapply':
	view_myapply();
	break;
case 'edit_myapply':
	edit_myapply();
	break;
case 'audit':
	audit_supplier_apply();
	break;
case 'supplierindustry':
	supplierindustry();
	break;
case 'suppliercategory':
	suppliercategory();
	break;
case 'supplierindustrylist':
	supplierindustrylist();
	break;
case 'suppliercategorylist':
	suppliercategorylist();
	break;
case 'supplierindustryedit':
	supplierindustryedit();
	break;
case 'suppliercategoryedit':
	suppliercategoryedit();
	break;
case 'getSupplierName':
	getSupplierName();
	break;
case 'getSupplierShortName':
	getSupplierShortName();
	break;
case 'supplier_export':
	supplier_export();
	break;
default:
	User::no_permission();
}

function supplierlist() {
	$supplier_list = new Supplier_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'search' => Security_Util::my_get('search')));
	echo $supplier_list->get_supplier_list_html();
	unset($supplier_list);
}

function supplierimport() {
	$supplier = new Supplier();
	echo $supplier->get_import_supplier_html();
	unset($supplier);
}

function supplieredit() {
	$supplier = new Supplier(array('id' => Security_Util::my_get('id')));
	echo $supplier->get_edit_supplier_html();
	unset($supplier);
}

function supplierapply(){
	$supplier = new Supplier();
	echo $supplier->get_supplier_apply_html();
	unset($supplier);
}

function mylist(){
	$supplier_apply_list = new Supplier_Apply_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'search' => Security_Util::my_get('search')));
	echo $supplier_apply_list->get_supplier_apply_list_html();
	unset($supplier_apply_list);
}

function view_myapply(){
	$supplier = new Supplier(array('id'=>Security_Util::my_get('id')));
	echo $supplier->view_myapply_html();
	unset($supplier);
}

function edit_myapply(){
	$supplier = new Supplier(array('id'=>Security_Util::my_get('id')));
	echo $supplier->edit_myapply_html();
	unset($supplier);
}

function audit_supplier_apply(){
	$supplier = new Supplier(array('id'=>Security_Util::my_get('id')));
	echo $supplier->audit_apply_html();
	unset($supplier);
}

function supplierindustry(){
	$supplier= new Supplier();
	echo $supplier->get_supplier_industry_html();
	unset($supplier);
}

function suppliercategory(){
	$supplier= new Supplier();
	echo $supplier->get_supplier_category_html();
	unset($supplier);
}

function supplierindustrylist(){
	$supplier_list = new Supplier_Industry_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'search' => Security_Util::my_get('search')));
	echo $supplier_list->get_supplier_industry_list_html();
	unset($supplier_list);
}

function suppliercategorylist(){
	$supplier_list = new Supplier_Category_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'search' => Security_Util::my_get('search')));
	echo $supplier_list->get_supplier_category_list_html();
	unset($supplier_list);
}

function supplierindustryedit(){
	$supplier = new Supplier(array('industry_id'=>Security_Util::my_get('id')));
	echo $supplier->edit_supplier_industry_html();
	unset($supplier);
}

function suppliercategoryedit(){
	$supplier = new Supplier(array('category_id'=>Security_Util::my_get('id')));
	echo $supplier->edit_supplier_category_html();
	unset($supplier);
}

function getSupplierName(){
	$q = Security_Util::my_get('q');
	$supplier = Supplier::getSupplierInstance();
	$s = '';
	foreach ($supplier as $val){
		if(stripos($val, $q) !== FALSE){
			$s .= $val . "\n";
		}
	}
	echo $s;
	
}

function getSupplierShortName(){
	$q = Security_Util::my_get('q');
	$ss = Supplier_Short::getInstance();
	$s = '';
	foreach ($ss as $val){
		if(stripos($val, $q) !== FALSE){
			$s .= $val . "\n";
		}
	}	
	echo $s;
}

function supplier_export(){
	$supplier= new Supplier();
	echo $supplier->getExportHtml();
	unset($supplier);
}