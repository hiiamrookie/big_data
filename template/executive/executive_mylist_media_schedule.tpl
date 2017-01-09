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
		<div class="crumbs">执行单管理系统</div>
		<div class="tab" id="tab"  style="height:30px">
        	<ul>
				<li class="on"><a>媒体排期表</a></li>
			</ul>
		</div>
        <div class="listform fix">
        	<table width="100%" class="tabin">
              <tr>
				<td>
                    &nbsp; 开始时间：<input type="text" onfocus="WdatePicker()" id="starttime" style=" width:80px;height:20px;" value="[STARTTIME]" />
                    &nbsp; 结束时间：<input type="text" onfocus="WdatePicker()" id="endtime" style=" width:80px;height:20px;" value="[ENDTIME]" />
                	&nbsp; 关键字: <input id="search" style=" width:150px;height:20px;" value="[SEARCH]"  /> 
                    &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
                </td>
              </tr>
            </table>
            
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="mylist">
                <thead>
                	<tr>
	                	<th width="5%">类型</th>
	                    <th width="10%">单号</th>
	                    <th width="20%">客户名称</th>
	                    <th width="15%">项目名称</th>
	                    <th width="6%">总金额</th>
	                    <th width="10%">总成本<font color="#0000FF">（预估）</font></th>
	                    <th width="14%">媒体排期表</th>
	                    <th width="5%">操作</th>
                	</tr>
                </thead>
                <tbody>
                	[EXECUTIVELIST]
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
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]executive/executive.js" language="javascript"></script>
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
			5:{sorter:"cu"}
		}
	});
});

function dosearch(){
  var starttime=$("#starttime").val();
  var endtime=$("#endtime").val();
  var searchcontent=$("#search").val();
  location.href=$.sprintf(base_url + "executive/?o=media_schedule&starttime=%s&endtime=%s&search=%s",starttime,endtime,searchcontent);
}
</script>
</body>
</html>