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
    <div class="crumbs">财务管理 - 开票申请</div>
    <div class="tab">
      <ul>
        <li><a href="?o=apply">开票申请</a></li>
        <li class="on"><a>已申请开票列表</a></li>
        [INVOICETAB]
      </ul>
    </div>
    <div class="listform fix">
      <table class="etable" cellpadding="0" cellspacing="0" border="0" id="ilist">
      <thead>
        <tr>
          <th style="width:30px">编号</th>
          <th style="width:120px">申请时间</th>
          <th>开票抬头</th>
          <th>开票金额</th>
          <th>开票类型</th>
          <th>所属公司</th>
          <th>开票号码</th>
          <th>状态</th>
          <th>驳回理由</th>
          <th>操作</th>
        </tr>
        </thead>
        <tbody>
        [INVOICELIST]
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

});
</script>
</body>
</html>