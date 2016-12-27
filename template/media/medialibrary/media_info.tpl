<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 媒体数据管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
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
				<li class="on"><a>媒体信息</a></li>
			</ul>
		</div>
                
        <div class="publicform fix">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="font-weight:bold; width:150px">媒体全称：</td>
                    <td>[NAME]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">媒体中文简称：</td>
                    <td>[CNAME]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">媒体英文简称：</td>
                    <td>[ENAME]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">URL：</td>
                    <td>[URL]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">媒体联络人：</td>
                    <td>[BANKINFO]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">媒体联络人：</td>
                    <td>[PERSON]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">媒体联系方式：</td>
                    <td>[CONTACT]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">代理商资质信息：</td>
                    <td>[DAILIINFO]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">政策信息：</td>
                    <td>[ZCINFO]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">账期/付款规则：</td>
                    <td>[PAYINFO]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">框架合同：</td>
                    <td>[CIDINFO]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">配送信息：</td>
                    <td>[SENDINFO]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">折扣：</td>
                    <td>[DISCOUNT]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">其他：</td>
                    <td>[OTHER]</td>
                </tr>
            </table>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script>
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
});
</script>
</body>
</html>
