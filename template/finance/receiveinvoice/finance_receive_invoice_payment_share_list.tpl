<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理系统</title>
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
    <div class="crumbs">财务管理</div>
    <div class="tab">
        <ul>
        	<li><a href="?o=receiveinvoicelist">收票对账单信息列表</a></li>
        	<li><a href="?o=receiveinvoiceadd">新建收票对账单信息</a></li>
        	<li><a href="?o=receiveinvoiceimport">收票对账单信息导入</a></li>
        	<li><a href="?o=pidsharelist">已分配执行单记录</a></li>
        	<li class="on"><a>已分配付款申请记录</a></li>
        	<li><a href="?o=virtualinvoiceshare">虚拟发票分配执行单</a></li>
        	<li><a href="?o=virtualinvoicesharepayment">虚拟发票分配付款申请</a></li>
        </ul>
    </div>
    
  <div class="listform fix">
    <table class="etable" cellpadding="0" cellspacing="0" border="0" width="950" id="flist">
    	<thead>
        <tr>
          <th>分配时间</th> 
          <th>成本</th>
          <th>进项</th>
          <th>价税合计金额</th>
          <th>操作</th>
        </tr>
        </thead>
        <tbody>
        [RECEIVEINVOICELIST]
        </tbody>
    </table>
    <div class="page_nav">
        <u>总记录：[ALLCOUNTS] 条 </u>
        <span class="page_nav_next">[NEXT]</span>
        <span class="page_nav_prev">[PREV]</span>
        <span class="page_nav_next">[COUNTS]</span>
    </div>
  </div>
  </div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
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

	$.tablesorter.addParser({
		id: "day", //指定一个唯一的ID
		is: function(s){
		   return false;
		},
		format: function(s){
		   return s.toLowerCase().replace(/天/,0); //将中文换成数字
		},
		type: "numeric" //按数值排序
	});
	
	$("#flist").tablesorter({
		headers:{
			1:{sorter:"cu"},
			2:{sorter:"cu"},
			3:{sorter:"cu"}
		}
	});
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });
});
</script>
</body>
</html>
