<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 系统客户编辑</title>
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
		<div class="crumbs">系统客户管理系统</div>
		<div class="tab">
			<ul>
				<li><a href="?o=customerlist">系统客户列表</a></li>
				<li class="on"><a>系统客户添加</a></li>
				<li><a href="?o=customerimport">系统客户批量关联</a></li>
			</ul>
		</div>
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]manage/action.php" target="post_frame">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
				<tr>
                	<td style="font-weight:bold" width="150">客户名称</td>
                    <td><input type="text" class="validate[required,maxSize[100]]" style="width:200px;height:20px;" name="customer_name" id="customer_name" /></td>
                </tr>
				<tr>
                	<td style="font-weight:bold" width="150">保险额度</td>
                    <td><input type="text" class="validate[required,custom[money]]" style="width:200px;height:20px;" name="safety" id="safety"/></td>
                </tr>
                <tr>
                	<td style="font-weight:bold" width="150">临时保险额度</td>
                    <td><input type="text" class="validate[optional,custom[money]]" style="width:200px;height:20px;" name="tmpsafety" id="tmpsafety"/></td>
                </tr>
                <tr>
                	<td style="font-weight:bold" width="150">临时保险额度截止日期</td>
                    <td><input type="text"  onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" style="width:100px" id="tmpsafety_deadline" name="tmpsafety_deadline" class="text Wdate" readonly="readonly"/></td>
                </tr>
			</table>
			<div class="btn_div"><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="customer_add"/><input type="submit" value="新 增" class="btn_sub" id="submit" /></div>
			</form>
			<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script src="[BASE_URL]script/My97DatePicker/WdatePicker.js"></script>
<script src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]manage/manage.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
$(document).ready(function() {	
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

	$("#tmpsafety").blur(function(){
		if($(this).val() > 0){
			$("#tmpsafety_deadline").addClass("validate[required,funcCall[check_deadline]]"); 
		}else{
			$("#tmpsafety_deadline").removeClass("validate[required,funcCall[check_deadline]]"); 
		}
	});
});

function check_deadline(field, rules, i, options){
	if($("#tmpsafety").val() == ""){
		return "请选择临时保险额度截止日期";
	}
}
</script>
</body>
</html>
