<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

//校验vcode
$vcode = $uid . User::SALT_VALUE;
include (dirname ( dirname ( __FILE__ ) ) . '/validate_vcode.php');

$result = FALSE;
switch (strval ( Security_Util::my_post ( 'action' ) )) {
	case 'media_add' :
		$result = media_do ( 'add' );
		break;
	case 'media_update' :
		$result = media_do ( 'update' );
		break;
}

if ($result !== FALSE) {
	if ($result ['status'] === 'error') {
		Js_Util::my_show_error_message ( $result ['message'] );
	} else if ($result ['status'] === 'success') {
		Js_Util::my_show_success_message ( $result ['message'] );
	}
} else {
	Js_Util::my_show_error_message ();
}

function media_do($action) {
	$fields = array ('name' => Security_Util::my_post ( 'name' ), 'url' => Security_Util::my_post ( 'url' ) );
	
	$cname = Security_Util::my_post ( 'cname' );
	$fields ['cname'] = ! empty ( $cname ) ? $cname : '';
	
	$ename = Security_Util::my_post ( 'ename' );
	$fields ['ename'] = ! empty ( $ename ) ? $ename : '';
	
	$bankinfo = Security_Util::my_post ( 'bankinfo' );
	$fields ['bankinfo'] = ! empty ( $bankinfo ) ? $bankinfo : '';
	
	$person = Security_Util::my_post ( 'person' );
	$fields ['person'] = ! empty ( $person ) ? $person : '';
	
	$contact = Security_Util::my_post ( 'contact' );
	$fields ['contact'] = ! empty ( $contact ) ? $contact : '';
	
	$dailiinfo = Security_Util::my_post ( 'dailiinfo' );
	$fields ['dailiinfo'] = ! empty ( $dailiinfo ) ? $dailiinfo : '';
	
	$zcinfo = Security_Util::my_post ( 'zcinfo' );
	$fields ['zcinfo'] = ! empty ( $zcinfo ) ? $zcinfo : '';
	
	$payinfo = Security_Util::my_post ( 'payinfo' );
	$fields ['payinfo'] = ! empty ( $payinfo ) ? $payinfo : '';
	
	$sendinfo = Security_Util::my_post ( 'sendinfo' );
	$fields ['sendinfo'] = ! empty ( $sendinfo ) ? $sendinfo : '';
	
	$cidinfo = Security_Util::my_post ( 'cidinfo' );
	$fields ['cidinfo'] = ! empty ( $cidinfo ) ? $cidinfo : '';
	
	$discount = Security_Util::my_post ( 'discount' );
	$fields ['discount'] = ! empty ( $discount ) ? $discount : '';
	
	$other = Security_Util::my_post ( 'other' );
	$fields ['other'] = ! empty ( $other ) ? $other : '';
	
	if ($action === 'update') {
		$fields ['media_id'] = Security_Util::my_post ( 'media_id' );
	}
	
	$media = new Media ( NULL, $fields );
	switch ($action) {
		case 'add' :
			return $media->add_media ();
			break;
		case 'update' :
			return $media->update_media ();
			break;
		default :
			return FALSE;
	}
}