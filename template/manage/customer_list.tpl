<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 我的执行单</title>
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
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">系统客户管理系统</div>
		<div class="tab" id="tab"  style="height:30px">
        	<ul>
				<li class="on"><a>系统客户列表</a></li>
				<li><a href="?o=customeradd">系统客户添加</a></li>
				<li><a href="?o=customerimport">系统客户批量关联</a></li>
			</ul>
		</div>
        <div class="listform fix">
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="mylist">
                <thead>
                	<tr>
	                    <th width="20%">客户名称</th>
	                    <th width="15%">保险额度</th>
	                    <th width="15%">临时保险额度<font color="#0000FF">（截至日期）</font></th>
	                    <th width="20%">关联状态</th>
	                    <th width="20%">已使用保险额度</th>
	                    <th>操作</th>
                	</tr>
                </thead>
                <tbody>
                	[CUSTOMERLIST]
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
<script src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script src="[BASE_URL]manage/manage.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
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
	
	$("#mylist").tablesorter({
		headers:{
			1:{sorter:"cu"},
			4:{sorter:"cu"}
		}
	});
});
</script>
</body>
</html>