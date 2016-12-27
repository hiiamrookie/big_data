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
    <div class="crumbs">财务管理</div>
    <div class="tab">
        <ul>
        	<li><a href="[BASE_URL]finance/supplier/?o=apply">供应商信息申请</a></li>
        	<li class="on"><a>已申请供应商信息列表</a></li>
        </ul>
    </div>
    
  <div class="listform fix">
  	<table width="100%" class="tabin">
                <tr>
                    <td >
                        &nbsp;&nbsp;关键字：<input type="text" name="search" id="search" style="height:20px;" value="[SEARCH]"/>
                        &nbsp;&nbsp;<input type="button" id="dosearch" value="搜 索" class="btn"/>
                    </td>
                </tr>
    </table>
    <table class="etable" cellpadding="0" cellspacing="0" border="0" width="950" id="flist">
    	<thead>
        <tr>
          <th width="50">编号</th>
          <th>申请时间</th>
          <th>供应商名称</th>
          <th>网址</th>
          <th>是否有抵扣联</th>
          <th>进票税率(%)</th>
          <th>供应商类型</th>
          <th>状态</th>
          <th>操作</th>
        </tr>
        </thead>
        <tbody>
        [SUPPLIERLIST]
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
	$("#flist").tablesorter();
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });

	$("#dosearch").click(function(){
		location.href = base_url + "finance/supplier/?o=mylist&search=" + $("#search").val();
	});
});
</script>
</body>
</html>
