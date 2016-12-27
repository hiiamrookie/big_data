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
               <li><a href="[BASE_URL]finance/deposit/?o=apply">保证金申请</a></li>
        		<li class="on"><a>已申请保证金列表</a></li>
        		<li><a href="[BASE_URL]finance/deposit/?o=deposit_invoice_apply">保证金票据申请</a></li>
        		<li><a href="[BASE_URL]finance/deposit/?o=my_deposit_invoice_list">已申请保证金票据列表</a></li>
      </ul>
    </div>
    <div class="listform fix">
      <table class="etable" cellpadding="0" cellspacing="0" border="0" id="ilist">
      	 <thead>
                	<tr>
	                	<th width="5%">编号</th>
	                    <th width="15%">申请时间</th>
	                    <th width="15%">所属合同</th>
	                    <th width="20%">客户名称</th>
	                    <th width="25%">保证金金额</th>
	                    <th>状态</th>
	                    <th width="5%">操作</th>
                	</tr>
                </thead>
                <tbody>
                	[DEPOSITLIST]
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
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
$(document).ready(function() {
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });
	
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
			4:{sorter:"cu"}
		}
	});
});
</script>
</body>
</html>
