<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 立项</title>
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
		<div class="crumbs">立项管理</div>
		<div class="tab">
			<ul>
				<li class="on"><a>审核立项</a></li>
			</ul>
		</div>
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]project/action.php" target="post_frame">
			<table class="sbd1" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="font-weight:bold" width="150">立项名称</td>
                    <td>[PROJECTNAME]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold" width="150">立项描述</td>
                    <td>[REMARK]</td>
                </tr>
                 <tr>
                    <td style="font-weight:bold" width="150">审核意见</td>
                    <td><textarea style="width:400px;height:80px" id="reason" class="validate[optional,maxSize[1000]] textarea" rows="3" name="reason"></textarea></td>
                </tr>
            </table>
            <div class="btn_div">
            	<input type="hidden" name="vcode" id="vcode" value="[VCODE]"/>
            	<input type="hidden" name="id" id="id" value="[ID]"/>
            	<input type="hidden" name="action" value="project_audit"/>
            	<input type="hidden" name="audit_pass" id="audit_pass" value="0"/>
            	<input id="confirm" type="button" value="审核确认" class="btn_qr"/>
                <input id="reject" type="button" value="审核驳回" class="btn_bh" />
            </div>
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
<script src="[BASE_URL]project/project.js"></script>
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
	
	$("#confirm").click(function(){ 
		$("#reason").removeClass("validate[required]");
		if(window.confirm("您确定要审核确认吗？")){
			$("#audit_pass").val("1");
			$("#formID").submit();
		}
	});
	
	$("#reject").click(function(){ 
		$("#reason").addClass("validate[required]");
		if(window.confirm("您确定要审核驳回吗？")){
			$("#audit_pass").val("0");
			$("#formID").submit();
		}
	});
});
</script>
</body>
</html>
