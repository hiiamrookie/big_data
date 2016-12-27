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
        		<li class="on"><a>供应商信息申请</a></li>
        		<li><a href="[BASE_URL]finance/supplier/?o=mylist">已申请供应商信息列表</a></li>
			</ul>
		</div>
       <div class="publicform fix">
         <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="font-weight:bold;width:100px">供应商名称</td>
                    <td><input type="text" name="supplier_name"  id="supplier_name" class="validate[required,maxSize[255]] text_new"/></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">网址</td>
                    <td><input type="text" name="url"  id="url" class="validate[optional,maxSize[1024],custom[url]] text_new" size="50"/></td>
                </tr>
                <!--tr>
                    <td style="font-weight:bold;width:100px">供应商产品分类</td>
                    <td><input type="text" name="category"  id="category" class="validate[optional,maxSize[100]] text_new" /></td>
                </tr-->
                <tr>
                    <td style="font-weight:bold;width:100px">是否有抵扣联</td>
                    <td><input type="radio" name="deduction"  value="0" checked/>&nbsp;无&nbsp;&nbsp;<input type="radio" name="deduction"  value="1"/>&nbsp;有</td>
                </tr>
                 <!--tr>
                    <td style="font-weight:bold;width:100px">返点比例</td>
                    <td><input type="text" name="rebate"  id="rebate" class="validate[optional,custom[number],min[0],max[100]] text_new"/>&nbsp;%</td>
                </tr-->
                <tr>
                    <td style="font-weight:bold;width:100px">进票税率</td>
                    <td><input type="text" name="in_invoice_tax_rate"  id="in_invoice_tax_rate" class="validate[required,custom[number],min[0],max[100]] text_new" value="0"/>&nbsp;%</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">供应商类型</td>
                    <td><input type="radio" name="supplier_type"  value="1" checked/>&nbsp;媒体&nbsp;&nbsp;<input type="radio" name="supplier_type"  value="2"/>&nbsp;外包</td>
                </tr>
                <!--tr>
                    <td style="font-weight:bold;width:100px">是否二级代理</td>
                    <td><input type="radio" name="isagent2"  value="0" checked/>&nbsp;否&nbsp;&nbsp;<input type="radio" name="isagent2"  value="1"/>&nbsp;是</td>
                </tr-->
                <tr>
                    <td style="font-weight:bold;width:100px">附件上传</td>
                    <td>
						<div>
                    		<input type="file" name="upfile" id="upfile" size="40" style="height:20px;"/>&nbsp;
                    		<input type="button" id="upload" value="上 传" onclick="up_uploadfile(this,'dids',0,0);" class="btn"/><input type="hidden" name="dids" id="dids" size="50" value="^"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font>
						</div>
					</td>
                </tr>
            </table>
            <div class="btn_div"><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="supplier_apply"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
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
	$('input[name="supplier_type"]').click(function(){
		if($(this).val()=="2"){
			//外包
			$("#url").removeClass("validate[required,maxSize[1024],custom[url]] text_new");
			$("#url").addClass("validate[optional,maxSize[1024],custom[url]] text_new");
		}else{
			//媒体
			$("#url").removeClass("validate[optional,maxSize[1024],custom[url]] text_new");
			$("#url").addClass("validate[required,maxSize[1024],custom[url]] text_new");
		}
	});
	
	$("#formID").validationEngine("attach",{ 
 		validationEventTrigger: "",
 		autoHidePrompt:true,
 		autoHideDelay:3000,
 		success: false,
 		promptPosition:"bottomRight", 
		scroll:false
	});

	$("input[name='deduction']").click(function(){
		if($(this).val() == "0"){
			$("#in_invoice_tax_rate").val("0");
		}
	});

	$("#in_invoice_tax_rate").blur(function(){
		if($('input[name="deduction"]:checked').val() == 0){
			$(this).val("0");
		}
	});
});
</script>
</body>
</html>