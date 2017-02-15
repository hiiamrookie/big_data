<!DOCTYPE HTML>
<html>
<head>
<title> 用户登录</title>
<meta charset="utf-8"/>
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/login.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/app.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
<style type="text/css">
.qrtab tr td{
	text-align:center;! important
	vertical-align:middle; ! important
}
</style>
</head>
<body>
<div id="main">
	<div class="title"><img src="[BASE_URL]images/login_logo.png" alt="" /></div>
	<form id="formID" method="post" action="[BASE_URL]login_action.php" target="post_frame">
	<div class="login">
		<label><span class="user"><em>用户名</em></span><input class="validate[required] text" type="text" name="username" id="username" /></label>
		<label><span class="pas"><em>密码</em></span><input class="validate[required] text" type="password" name="password" id="password" /></label>
		<div class="submit_button"><input type="hidden" name="vcode" value="[VCODE]"><input type="submit" class="btn" id="submit" value="登 录" /></div>
	</div>
	</form>
	<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
	<!-- <h6>上海网迈广告有限公司 Copyright &copy; 2004-2011 nimads.All Rights Reserved</h6> -->
</div>
<div class="overlay3">
	<div class="scbox3">
		<table class="qrtab">
			<tr><td><img src="[BASE_URL]images/[QRCODE]"></td></tr>
			<tr><td><a href="[BASE_URL]app/app_user_manual.doc">用户使用说明下载</a></td></tr>
		</table>
	</div>
	<img src="[BASE_URL]images/none.gif" class="close" onclick="close_pop();"/>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script src="[BASE_URL]js/common.js"></script>
<script>
$(document).ready(function() {
	$('body').css({'height': parseInt($(document).height())});
	
	$("#username").focus();
	
    $("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

    $("#down").click(function(){
    	$(".overlay3").before('<div id="exposeMask"></div>').show();
    });
});

function close_pop(){
	$(".overlay3").hide();
	$("#exposeMask").remove();
}
</script>
</body>
</html>
