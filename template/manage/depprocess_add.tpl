<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 部门流程设定</title>
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
		<div class="crumbs">流程管理系统 - 部门流程编辑</div>
		<div class="tab">
			<ul>
            	<li><a href="?o=depprocesslist">部门流程列表</a></li>
				<li class="on"><a>部门流程添加</a></li>
			</ul>
		</div>
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]manage/action.php" target="post_frame">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                	<td width="150">部门选择：</td>
                	<td>
                    	<select class="validate[required] select" id="dep" name="dep" style="width:150px;">
                        	[DEPLIST]
                        </select>
                    </td>
                </tr>
				<tr>
                	<td>名称：</td>
                    <td><input type="text" class="validate[required,maxSize[50]]" style="width:35%;height:20px;" name="name" id="name" /></td>
                </tr>
				<tr>
                	<td>描述：</td>
                    <td><textarea class="validate[optional,maxSize[500]] textarea" rows="2" style="width:35%;height:40px" name="des" id="des"></textarea></td>
                </tr>
                <tr>
                	<td>流程内容：</td>
                    <td>
                    	<input type="button" value="添 加" class="btn" id="adddepprocesscontent" /><input type="hidden" name="contents" id="contents" value=","/><br />
                        <div id="depprocesscontentlist"></div>
                    </td>
                </tr>
			</table>
			<div class="btn_div"><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="dep_process_add"/><input type="submit" value="新 增" class="btn_sub" id="submit" /></div>
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
<script src="[BASE_URL]manage/manage.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var ss='[DEPLIST]';
var c = 1;
$(document).ready(function() {	
	$("#adddepprocesscontent").click(adddepprocesscontent);
	
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