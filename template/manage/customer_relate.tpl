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
				<li class="on"><a>系统客户关联</a></li>
				<li><a href="?o=customerimport">系统客户批量关联</a></li>
			</ul>
		</div>
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]manage/action.php" target="post_frame">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
				<tr>
                	<td style="font-weight:bold" width="150">客户名称</td>
                    <td>[CUSTOMERNAME]</td>
                </tr>
				<tr>
                	<td style="font-weight:bold" width="150">保险额度</td>
                    <td>[SAFETY]</td>
                </tr>
                <tr>
                	<td style="font-weight:bold" width="150">已关联OA客户</td>
                    <td>[OACUSNAMES]</td>
                </tr>
                <tr>
                	<td style="font-weight:bold" width="150">搜索OA客户</td>
                    <td><input type="text" name="search" id="search" style="width:200px;height:20px;"/>&nbsp;<input type="button" value="搜 索" class="btn" id="searchbtn"/></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                    <td id="search_result"></td>
                </tr>
			</table>
			<div class="btn_div"><input type="hidden" name="customer_id" id="customer_id" value="[CUSTOMERID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="customer_relate"/><input type="submit" value="提 交" class="btn_sub" id="submit" /></div>
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
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {	
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

	$("#searchbtn").click(function(){
		var val = $.trim($("#search").val());
		if(val == ""){
			alert("请输入关键字");
		}else{
			$.ajax({
				type: "POST",
				url: "do.php",
				cache:"false",
				data: "action=search_cusname&search=" + $("#search").val() + "&t=" + Math.random() + "&vcode=" + vcode,
				dataType:'text',
				async: false,
				success: function(msg){
					$("#search_result").empty();
					$("#search_result").append(msg);
				},
			 	error: function(e){
			 		alert("搜索OA客户记录异常");
			 	}
			});
		}
	});
});
</script>
</body>
</html>
