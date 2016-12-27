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
        	<li class="on"><a>当月收款列表</a></li>
            <li><a href="?o=receivables">收款登记</a></li>
            <li><a href="?o=receivables_search">收款信息查询</a></li>
            <li><a href="?o=receivables_import">收款信息导入</a></li>
        </ul>
    </div>
    
  <div class="listform fix">
  	<table width="100%" class="tabin">
      <tr>
        <td>
            &nbsp; 关键字: &nbsp;<input id="search" style=" width:150px;height:20px;" value="[SEARCH]"  /> 
            &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
        </td>
      </tr>
    </table>
    <table class="etable" cellpadding="0" cellspacing="0" border="0" width="950" id="flist">
    	<thead>
        <tr>
          <th width="50">编号</th>
          <th>收款日期</th>
          <th>收款金额</th>
          <th>分配执行单</th>
          <th>付款人名称</th>
          <th>登记人</th>
          <th>登记时间</th>
          <th>状态</th>
          <th>操作</th>
        </tr>
        </thead>
        <tbody>
        [RECEIVABLESLIST]
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
var vcode = '[VCODE]';
$(document).ready(function() {
	$("#flist").tablesorter();
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });
	$("#dosearch").live("click",function () { 
		var searchcontent = $("#search").val();
		location.href = $.sprintf(base_url + "finance/receivables/?o=receivableslist&search=%s",searchcontent);
	});
});

function docancel(id){
	if(window.confirm("确定撤销该收款记录？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=cancel_receivable&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				  alert(msg);
			   },
		 	   error: function(e){
		 		   alert("撤销收款记录异常");
		 	   }
		});
		location.href="[BASE_URL]finance/receivables/?o=receivableslist";
	}
}
 
</script>
</body>
</html>
