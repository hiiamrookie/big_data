<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
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
  <div class="nav_top"> [TOP] </div>
  <div id="content" class="fix">
    <div class="crumbs">财务管理 - 统计分析</div>
    <div class="tab" id="tab">
      <ul>
        <li class="on"><a href="?o=tjall">统计分析</a></li>
      </ul>
    </div>
    
    <div class="publicform fix" style="width:100%;" >
      <table cellpadding="0" cellspacing="0" border="0" style="width:100%" class="sbd1" >
      	<tr>
        	<td style="text-align:center; height:40px; vertical-align:middle" >
            	<select id="city" name="city" class="select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="select"><option value="">请选择部门</option></select>&nbsp;<select id="team" name="team" class="select"><option value="">请选择团队</option></select> &nbsp;&nbsp;&nbsp;&nbsp;
                时间选择：&nbsp;&nbsp;
            	<input type="text" onclick="WdatePicker( {dateFmt:'yyyy-MM'} )" width=100 class="text Wdate" id="starttime" >
            	&nbsp; - &nbsp;
            	<input type="text" onclick="WdatePicker( {dateFmt:'yyyy-MM'} )" width=100 class="text Wdate" id="endtime">
            </td>
        </tr>
        <tr>
        	<td style="text-align:center; height:40px; vertical-align:middle">
            	<input type="button" id="submit1" value="下载应收报表" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" id="submit2" value="下载应付报表" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" id="submit3" value="下载收入成本配比报表" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" id="submit4" value="下载项目执行表" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
        </tr>
      </table>
    </div>
  </div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
var base_url = '[BASE_URL]';
$(document).ready(function() {
	//从地区选择部门
    $("#city").live("change",function(){ 
        $("#dep").html('<option value="">请选择部门</option>');
        $("#team").html('<option value="">请选择团队</option>');
        getdepsbycity(this,base_url,vcode);
    });
    
    //从部门选择团队
    $("#dep").live("change",function(){ 
    	$("#team").html('<option value="">请选择团队</option>');
        getteamsbydep(this,base_url,vcode); 
    });
    
	$("#submit1").click( function(){ if (!confirm("确认要下载应收报表吗？")) return; alert("下载数据可能较大，请耐心等候！"); location.href="arlist.php";});	
	$("#submit2").click( function(){ if (!confirm("确认要下载应付报表吗？")) return; alert("下载数据可能较大，请耐心等候！"); location.href="aplist.php";});
	$("#submit3").click( function(){ if (!confirm("确认要下载收入成本配比表吗？")) return; alert("下载数据可能较大，请耐心等候！"); location.href="revenuescostlist.php";});
	$("#submit4").click( function(){ if (!confirm("确认要下载项目执行分析报表吗？")) return; alert("下载数据可能较大，请耐心等候！"); location.href="biglist.php";});
});
</script>
</body>
</html>
