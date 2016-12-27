<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 执行单系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">执行单管理系统 - 执行单管理</div>
		<div class="tab" id="tab" style="height:30px">
        	<ul>
			<li><a href="?o=manage">执行单管理</a></li>
			<li><a href="?o=userchange">执行单人员变更</a></li>
			<li class="on"><a>执行单统计</a></li>
		</ul>
		</div>
        
        <div class="listform fix">
        	<table width="100%" class="tabin">
              <tr>
				<td>
                    &nbsp; 月份选择：<input type="text" onfocus="WdatePicker({dateFmt:'yyyy-MM'})" id="month" style=" width:80px" value="[MONTH]" />
                    &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
                </td>
              </tr>
            </table>
            
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="example">
                <tr>
                	<th width="15%">日期</th>
                    <th width="15%">单数</th>
                    <th width="70%">[ALLCOUNTS] </th>
                </tr>
                [EXECUTIVETJ]
                <!--[CUT]
                <tr>
                	<td>[DATE]</td>
                    <td>[COUNT]</td>
                    <td>&nbsp;</td>
                </tr>
                [/CUT]-->
            </table>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]executive/executive.js" language="javascript"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
$(document).ready(function() {
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });
	
	$("#dosearch").live("click",function () { dosearch(); });
	
});

function dosearch(){
	var month=$("#month").val();
	location.href=$.sprintf(base_url + "executive/?o=tj&month=%s",month);
}
</script>
</body>
</html>
