<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 密码修改</title>
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
		<div class="crumbs">个人信息管理</div>
		<div class="tab">
			<ul>
				<li class="on"><a>密码修改</a></li>
			</ul>
		</div>
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]hr/action.php" target="post_frame">
			<table class="sbd1" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="font-weight:bold" width="150">用户名</td>
                    <td>[USERNAME] <span id='uid' style='display:none'>[UID]</span></td>
                </tr>
                <tr>
                    <td style="font-weight:bold" width="150">原密码</td>
                    <td><input class="validate[required] rb3" type="password" name="oldpwd" id="oldpwd" style="height:18px; width:130px"/></td>
                </tr>
                <tr>
                    <td style="font-weight:bold" width="150">新密码</td>
                    <td><input class="validate[required] rb3" type="password" name="pwd" id="pwd" style="height:18px; width:130px"/></td>
                </tr>
                <tr>
                    <td style="font-weight:bold" width="150">重复新密码</td>
                    <td><input class="validate[required,equals[pwd]] rb3" type="password" name="repwd" id="repwd" style="height:18px; width:130px"/></td>
                </tr>
            </table>
            <div class="btn_div"><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="changepwd"/><input type="submit" value="提 交" class="btn_sub" id="submit" /></div>
            </form>
            <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#side_nav h2").click(function(){
		if($(this).hasClass("current")){return;}
        	else{
				$("#side_nav h2").removeClass("current");
                $("#side_nav ul").removeClass("pane");
                $("#side_nav ul").slideUp("fast");
                $(this).addClass("current");
                $(this).next("ul").addClass("pane");
                $(this).next("ul").slideDown(0);
			}
        }).eq(menu_o).click();	

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
