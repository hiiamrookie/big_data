<?php
$finance_report = new Finance_Report(
		array('starttime' => min($seldate),
				'endtime' => max($seldate)
		));
$finance_report->get_finance_report();



			$results = $this->db->get_results ( 'SELECT b.id,b.pid,b.cid,b.city,b.dep,b.team,b.name,b.type,b.amount,b.allcost,b.starttime,b.endtime,b.company,b.time,b.paytimeinfoids,b.isalter,FROM_UNIXTIME(b.oktime) AS oktimeshow FROM 
(
SELECT a.id,a.pid,a.cid,a.city,a.dep,a.team,a.isalter,a.isok,a.oktime,a.name,a.type,a.amount,a.allcost,a.starttime,a.endtime,a.company,a.time,a.paytimeinfoids
FROM (
SELECT id,pid,cid,city,dep,team,isalter,isok,oktime,name,type,amount,allcost,starttime,endtime,company,time,paytimeinfoids FROM executive WHERE isok=1 AND oktime>=' . $this->starttime . ' AND oktime<=' . $this->endtime . ' ORDER BY pid,isalter DESC
) a GROUP BY pid
) b' );