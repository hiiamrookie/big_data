<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>数据导出</title>
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
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">数据管理系统</div>
		<div class="tab" id="tab">
			<ul>
				<li><a href="[BASE_URL]report/?o=own_data_import">广告主自有数据导入</a></li>
				<li><a href="[BASE_URL]report/?o=third_data_import">第三方数据导入</a></li>
				<li class="on"><a>数据导出</a></li>
			</ul>
		</div>
        <!--div class="listform fix"-->
        <div class="publicform fix">
         <form id="formID" method="post" action="[BASE_URL]report/action.php" target="post_frame">
	         <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
	         [TABLE]
	         	<tr>
	         		<td style="font-weight:bold;width:150px;">数据项</td>
	         		<td>
	         			<input type="checkbox" name="items[]" value="pv" checked="checked">&nbsp;PV&nbsp;&nbsp;
	         			<input type="checkbox" name="items[]" value="uv" checked="checked">&nbsp;UV&nbsp;&nbsp;
	         			<input type="checkbox" name="items[]" value="impressions" checked="checked">&nbsp;Impressions&nbsp;&nbsp;
	         			<input type="checkbox" name="items[]" value="click" checked="checked">&nbsp;Click&nbsp;&nbsp;
	         			<input type="checkbox" name="items[]" value="ctr" checked="checked">&nbsp;CTR
	         		</td>
	         	<tr/>
	         </table>
            <div class="btn_div"><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="data_export"/><input type="submit" value="导 出" class="btn_sub" id="submit" />
			</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
        </div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script src="[BASE_URL]script/jquery.sprintf.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]report/report.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
$(document).ready(function() {
	$("#formID").validationEngine("attach",{ 
 		validationEventTrigger: "",
 		autoHidePrompt:true,
 		autoHideDelay:3000,
 		success: false,
 		promptPosition:"bottomRight", 
		scroll:false
	});
});
</script>
</body>
</html>
