<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理系统</title>
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
		<div class="crumbs">财务管理</div>
		<div class="tab" id="tab">
			<ul>
				<li><a href="?o=supplierlist">供应商信息列表</a></li>
        		<li class="on"><a>供应商信息导入</a></li>
        		<li><a href="?o=supplierindustry">新建客户行业分类</a></li>
        		<li><a href="?o=supplierindustrylist">客户行业分类列表</a></li>
        		<li><a href="?o=suppliercategory">新建供应商产品分类</a></li>
        		<li><a href="?o=suppliercategorylist">供应商产品分类列表</a></li>
        		<li><a href="?o=supplier_export">信息导出</a></li>
			</ul>
		</div>
        <div class="listform fix">
         <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame" enctype="multipart/form-data">
        	<table width="100%" class="tabin">
                <tr>
                    <td >
                        &nbsp;&nbsp;选择文件：
                        <input type="file" name="upfile" id="upfile" class="validate[required]"/>
                         &nbsp;&nbsp;<input type="submit" value="上 传" class="btn"/>&nbsp;&nbsp;<input type="button" value="&nbsp;下载模板&nbsp;" id="dt" class="longbtn"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATEFILE] 类型的文件，且单个文件最多 [MAXFILESIZE]M</font><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="supplier_import"/>
                    </td>
                </tr>
            </table>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
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

	$("#dt").click(function(){
		location.href= base_url + "download_template.php?type=supplier";
	});
});
</script>
</body>
</html>
