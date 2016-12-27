<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/tablesorter.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
  <div class="nav_top"> [TOP] </div>
  <div id="content" class="fix">
    <div class="crumbs">财务管理 - 保证金管理</div>
    <div class="tab">
      <ul>
        <li [ON1]><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=1">待审核待打印</a></li>
        <li [ON3]><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=3">已打印待归档</a></li>
        <li [ON2]><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=2">当月已审核</a></li>
        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoice_search">开票信息查询</a></li>
        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoice_import">开票信息导入</a></li>
        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivableslist">当月保证金收款列表</a></li>
        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivables">保证金收款登记</a></li>
        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivables_search">保证金收款信息查询</a></li>
        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivables_import">保证金收款信息导入</a></li>
      </ul>
    </div>
    <div class="listform fix">
    	[SEARCHBAR]
      <table class="etable" cellpadding="0" cellspacing="0" border="0" id="ilist">
      	<thead>
      	[LISTTITLE]
        </thead>
        <tbody>
        [DEPOSITLIST]
        </tbody>
      </table>
    </div>
  </div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
$(document).ready(function() {
	$.tablesorter.addParser({
		id: "cu", //指定一个唯一的ID
		is: function(s){
		   return false;
		},
		format: function(s){
		   return s.toLowerCase().replace(/￥/,"").replace(/,/g,"");
		},
		type: "numeric" //按数值排序
	});
	$("#ilist").tablesorter({
		headers:{
			3:{sorter:"cu"}
		}
	});
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });

	$("#searchbtn").click(function(){
		location.href = base_url + "finance/deposit/?o=deposit_invoicelist&d=2&search=" + $("#search").val();
	});
});
</script>
</body>
</html>
