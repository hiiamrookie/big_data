<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
  <div class="nav_top"> [TOP] </div>
  <div id="content" class="fix">
    <div class="crumbs">财务管理 - 财务大表</div>
    <div class="tab" id="tab">
      <ul>
        <li class="on"><a>财务大表</a></li>
      </ul>
    </div>
   <div class="listform fix">
    <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
        	<table width="100%" class="tabin" cellpadding="5" cellspacing="5" border="0">
                <tr>
                    <td colspan="2">
                        <!--&nbsp;&nbsp;起始：
                        <input type="text" class="validate[required] text Wdate" name="starttime" id="starttime" style="width:100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" readonly="readonly"/> 
                        &nbsp;终止：
                        <input type="text" class="validate[required] text Wdate" name="endtime" id="endtime" style="width:100px" onclick="WdatePicker({minDate:'#F{$dp.$D(\'starttime\')}'})" readonly="readonly"/-->
                       &nbsp;&nbsp;<input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action"  value="finance_report"/><input type="button" id="doexport" value="&nbsp;生成报表&nbsp;" class="longbtn"/>
                    </td>
                </tr>
                [SETTLEACCOUNTDATES]
            </table>
    </form>
    <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
  </div>
</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
var base_url = '[BASE_URL]';
$(document).ready(function() {
	$("#doexport").click(function(){
		var da = new Array();
		$('input[name="seldate[]"]').each(function(){
			if($(this).attr("checked")=="checked"){
				da.push($(this).val());
			}
		});
		if(da.length != 2){
			alert("请选择2个时间点来确定报表生成条件");
		}else{
			$("#formID").submit();
		}
	});
});
</script>
</body>
</html>
