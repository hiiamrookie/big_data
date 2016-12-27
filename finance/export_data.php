<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');


$user_login = new User_Login('jesse','nim1101');
header('Content-type: text/html; charset=utf-8');
$endtime = strtotime('2016-04-25 23:59:59');
$starttime   = strtotime('2015-12-16 17:54:38');
/*$endtime = strtotime('2017-04-25 23:59:59');
$starttime   = strtotime('2017-02-25 10:00:00');*/
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(0);
$finance_report = new Finance_Report(
		array('starttime' => $starttime,
				'endtime' => $endtime
		));
$finance_report->out_get_finance_report();


