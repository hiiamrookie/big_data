<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

//校验vcode
$vcode = $uid . User::SALT_VALUE;
if (! crumb::verifyCrumb ( $vcode, Security_Util::my_get ( 'vcode' ) )) {
	header ( 'Content-type: text/html; charset=utf-8' );
	$back_url = $_SERVER ['HTTP_REFERER'];
	if (empty ( $back_url )) {
		$back_url = BASE_URL;
	}
	echo INVALIDATION_VISIT;
	exit ();
}

switch (strval ( Security_Util::my_get ( 'o' ) )) {
	case 'add' :
		echo booking_do ( 'add' );
		break;
	case 'del' :
		echo booking_do ( 'del' );
		break;
	default:
		echo '操作有误';
}

function booking_do($action) {
	if($action === 'add'){
		$fields = array('st'=>Security_Util::my_get('start'),'en'=>Security_Util::my_get('end'),'title'=>Security_Util::my_get('title'),'type'=>Security_Util::my_get('type'),'telmeeting'=>Security_Util::my_get('telmeeting'),'telmeeting_type'=>Security_Util::my_get('telmeeting_type'));
	}else if($action === 'del'){
		$fields = array('id'=>Security_Util::my_get('id'));
	}
	
	$booking = new Booking($fields);
	switch($action){
		case 'add':
			return $booking->booking_add();
			break;
		case 'del':
			return $booking->booking_del();
			break;
	}
}