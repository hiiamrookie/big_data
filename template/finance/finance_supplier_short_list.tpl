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
		<div class="crumbs">财务管理 - 媒体简称列表</div>
		<div class="tab" id="tab"  style="height:30px">
        	<ul>
				<li><a  href="[BASE_URL]finance/?o=meida_short">媒体简称设置</a></li>
        		<li class="on"><a>媒体简称列表</a></li>
        		<li><a href="[BASE_URL]finance/?o=meida_short_export">媒体简称导出</a></li>
			</ul>
		</div>
        <div class="listform fix">
        	<table width="100%" class="tabin">
              <tr>
				<td>
                   	&nbsp; 关键字: <input id="search" style=" width:150px;height:20px;" value="[SEARCH]"  /> 
                    &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
                </td>
              </tr>
            </table>
            
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="mylist">
                <thead>
                	<tr>
	                	<th width="10%">编号</th>
	                    <th width="40%">媒体简称</th>
	                    <th width="40%">创建时间</th>
	                    <th width="10%">操作</th>
                	</tr>
                </thead>
                <tbody>
                	[SUPPLIERSHORTLIST]
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
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
$(document).ready(function() {
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });
	
	$("#dosearch").live("click",function () { dosearch(); });

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
			4:{sorter:"cu"},
			5:{sorter:"cu"},
			7:{sorter:"day"}
		}
	});
});

function dosearch(){
  var searchcontent=$("#search").val();
  location.href=$.sprintf(base_url + "finance/?o=meida_short_list&search=%s",searchcontent);
}
</script>
</body>
</html>