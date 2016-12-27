<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 账户添加</title>
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
	<div class="nav_top">
		[TOP]
	</div>
	<div id="content" class="fix">
		<div class="crumbs">人事管理系统</div>
		<div class="tab" id="tab">
			<ul>
            	<li><a href="?o=userlist">账户列表</a></li>
				<li class="on"><a>账户添加</a></li>
			</ul>
		</div>
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]hr/action.php" target="post_frame">
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
            	<tr>
					<td style="width:150px;">登录名（邮箱名前缀）</td>
					<td><input class="validate[required,maxSize[100]]" type="text" style="width:200px;height:20px;" id="username" name="username"/> </td>
				</tr>
                <tr>
					<td>真实姓名</td>
					<td><input class="validate[required,maxSize[100]]" type="text" style="width:200px;height:20px;" id="realname" name="realname"/></td>
				</tr>
                <tr>
					<td>性别</td>
					<td>
						<input type="radio" name="sex" id="sex" class="radio" checked="checked" value="1"/><label style="width:30px">男</label>
						<input type="radio" name="sex" id="sex" class="radio" value="2"/><label>女</label>
					</td>
				</tr>
                <tr>
					<td>手机号码</td>
					<td><input class="validate[required,custom[phone]]" type="text" style="width:200px;height:20px;" id="mobile" name="mobile"/></td>
				</tr>
                <tr>
					<td>邮箱</td>
					<td><input class="validate[required,custom[email]]" type="text" style="width:200px;height:20px;" id="email" name="email"/></td>
				</tr>
                <tr>
					<td>所属地区/部门/团队</td>
					<td>
                        <select id="city" name="city" class="validate[required] select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="validate[required] select"><option value="">请选择部门</option></select>&nbsp;<select id="team" name="team" class="select"><option value="">请选择团队</option></select>
                    </td>
				</tr>
                <tr>
                    <td>权限</td>
                    <td>[PERMISSIONS]</td>
                </tr>
			</table>
			<div class="btn_div"><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="user_add"/><input type="submit" value="提 交" class="btn_sub" id="submit" /></div>
			</form>
			<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]hr/hr.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
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
});

</script>
</body>
</html>
