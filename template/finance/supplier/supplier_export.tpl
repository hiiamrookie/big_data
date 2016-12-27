<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
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
		<div class="crumbs">财务管理 - 信息导出</div>
		<div class="tab" id="tab" style="height:30px">
        	<ul>
        		<li><a href="?o=supplierlist">供应商信息列表</a></li>
        		<li><a href="?o=supplierimport">供应商信息导入</a></li>
        		<li><a href="?o=supplierindustry">新建客户行业分类</a></li>
        		<li><a href="?o=supplierindustrylist">客户行业分类列表</a></li>
        		<li><a href="?o=suppliercategory">新建供应商产品分类</a></li>
        		<li><a href="?o=suppliercategorylist">供应商产品分类列表</a></li>
        		<li class="on"><a>信息导出</a></li>
      		</ul>
		</div>
       <div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
			<table class="sbd1" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="font-weight:bold" width="150">导出类型</td>
                    <td>
						<input type="radio" name="exportype" value="0" checked="checked"/>&nbsp;供应商名称&nbsp;&nbsp;
						<input type="radio" name="exportype" value="1"/>&nbsp;客户行业分类&nbsp;&nbsp;
						<input type="radio" name="exportype" value="2"/>&nbsp;供应商产品分类&nbsp;&nbsp;
					</td>
                </tr>
            </table>
            <div class="btn_div"><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="supplier_type_export"/><input type="submit" value="导出" class="btn_sub" id="sub" /></div>
            </form>
            <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
var vcode = "[VCODE]";
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
