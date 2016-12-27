<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 媒体数据管理</title>
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
		<div class="crumbs">媒体数据管理 - 媒体库信息</div>
		<div class="tab" id="tab">
			<ul>
            	<li><a href="?o=mtlist">媒体库列表</a></li>
				<li class="on"><a>新建媒体信息</a></li>
			</ul>
		</div>
                
        <div class="publicform fix">
        	<form id="formID" method="post" action="[BASE_URL]media/action.php" target="post_frame">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="font-weight:bold; width:150px">媒体全称：</td>
                    <td><input type="text" style="width:200px" name="name" id="name" class="validate[required,maxSize[200]] text" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">媒体中文简称：</td>
                    <td><input type="text" style="width:200px" name="cname" id="cname" class="validate[optional,maxSize[200]] text" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">媒体英文简称：</td>
                    <td><input type="text" style="width:200px" name="ename" id="ename" class="validate[optional,maxSize[200]] text" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">URL：</td>
                    <td><input type="text" style="width:300px" name="url" id="url" class="validate[required,custom[url]] text" value="http://" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">银行账号信息：</td>
                    <td><input type="text" style="width:300px" name="bankinfo" id="bankinfo" class="validate[optional,maxSize[500]] text" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">媒体联络人：</td>
                    <td><input type="text" style="width:300px" name="person" id="person" class="validate[optional,maxSize[500]] text" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">联系方式：</td>
                    <td><input type="text" style="width:300px" name="contact" id="contact" class="validate[optional,maxSize[500]] text" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">代理商资质信息：</td>
                    <td><textarea name="dailiinfo" id="dailiinfo" class="validate[optional,maxSize[500]] text" style="width:300px; height:60px;"></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">政策信息：</td>
                    <td><textarea name="zcinfo" id="zcinfo" class="validate[optional,maxSize[500]] text" style="width:300px; height:60px;"></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">账期/付款规则：</td>
                    <td><textarea name="payinfo" id="payinfo" class="validate[optional,maxSize[500]] text" style="width:300px; height:60px;"></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">框架合同：</td>
                    <td><textarea name="cidinfo" id="cidinfo" class="validate[optional,maxSize[500]] text" style="width:300px; height:60px;"></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">配送信息：</td>
                    <td><textarea name="sendinfo" id="sendinfo" class="validate[optional,maxSize[500]] text" style="width:300px; height:60px;"></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">折扣：</td>
                    <td><textarea name="discount" id="discount" class="validate[optional,maxSize[500]] text" style="width:300px; height:60px;"></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">其他：</td>
                    <td><textarea name="other" id="other" class="validate[optional,maxSize[500]] text" style="width:300px; height:60px;"></textarea></td>
                </tr>
            </table>
            <div class="btn_div"><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="media_add"/><input type="submit" id="submit" value="提 交" class="btn_sub" /></div>
        	</form>
        	<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
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
	 }).eq(menu_m).click();
	 
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
