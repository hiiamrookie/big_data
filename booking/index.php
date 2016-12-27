<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'json':
	getjson();
	break;
default:
	showindex();
}

function getjson() {
	$start = Security_Util::my_get('st');
	$end = Security_Util::my_get('en');
	if (date_default_timezone_get() !== 'PRC') {
		date_default_timezone_set('PRC');
	}

	if (empty($start)) {
		$start = 0;
	} else {
		$start = strtotime(date('Y-m-d H:i:s', $start) . ' + 8 hours');
	}
	if (empty($end)) {
		$end = 0;
	} else {
		$end = strtotime(date('Y-m-d H:i:s', $end) . ' + 8 hours');
	}
	$fields = array('type' => Security_Util::my_get('type'), 'st' => $start,
			'en' => $end);
	$booking = new Booking($fields);
	echo $booking->get_json();
}

function showindex() {
	$booking = new Booking(array('type' => Security_Util::my_get('type')));
	echo $booking->get_booking_index_html();
}
