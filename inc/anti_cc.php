<?php
$ll_nowtime = time();
if (isset($_SESSION['ll_lasttime'])) {
	$ll_lasttime = $_SESSION['ll_lasttime'];
	$ll_times = $_SESSION['ll_times'] + 1;
	$_SESSION['ll_times'] = $ll_times;
} else {
	$ll_lasttime = $ll_nowtime;
	$ll_times = 1;
	$_SESSION['ll_times'] = $ll_times;
	$_SESSION['ll_lasttime'] = $ll_lasttime;
}

if (($ll_nowtime - $ll_lasttime) < 3) {
	if ($ll_times >= 5) {
		header('Location: http://www.baidu.com');
		exit();
	}
} else {
	$ll_times = 0;
	$_SESSION['ll_lasttime'] = $ll_nowtime;
	$_SESSION['ll_times'] = $ll_times;
}