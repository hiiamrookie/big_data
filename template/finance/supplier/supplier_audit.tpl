<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理系统</title>
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
        		<li class="on"><a>供应商信息审核</a></li>
			</ul>
		</div>
       <div class="publicform fix">
         <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="font-weight:bold;width:100px">供应商名称</td>
                    <td>[SUPPLIERNAME]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">网址</td>
                    <td>[URL]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">是否有抵扣联</td>
                    <td>[DEDUCTION]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">进票税率</td>
                    <td>[ININVOICETAXRATE]&nbsp;%</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">供应商类型</td>
                    <td>[SUPPLIERTYPE]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">附件上传</td>
                    <td>
						<div>
                    		[DIDS]
						</div>
					</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">申请人</td>
                    <td>[APPLYUSER]</td>
                </tr>
                <tr>
                	<td style="font-weight:bold;width:100px">实际供应商对应</td>
                	<td><select name="parentid" id="parentid" class="select" style="width:200px;">[PARENTSUPPLIERS]</select></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">审核状态</td>
                    <td><input type="radio" name="audit_result" value="1" checked/>&nbsp;通过&nbsp;&nbsp;<input type="radio" name="audit_result" value="-1"/>&nbsp;驳回</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">审核留言</td>
                    <td><textarea style="width:400px;height:80px" id="remark" class="validate[optional,maxSize[500]] textarea" rows="3" name="remark"></textarea></td>
                </tr>
            </table>
            <div class="btn_div"><input type="hidden" name="id" value="[ID]"/><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="supplier_audit"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
			</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
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
});
</script>
</body>
</html>