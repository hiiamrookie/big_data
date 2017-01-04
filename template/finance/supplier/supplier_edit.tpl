<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理</div>
		<div class="tab">
			<ul>
            	<li><a href="?o=supplierlist">供应商信息列表</a></li>
        		<li class="on"><a>供应商信息修改</a></li>
			</ul>
		</div>
        <div class="publicform fix">
        	<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1" style="white-space:nowrap">
            	<tr>
					<td style="font-weight:bold;width:100px">供应商名称</td>
                    <td><input type="text" name="supplier_name"  id="supplier_name" class="validate[required,maxSize[255]] text_new" value="[SUPPLIERNAME]"/></td>
                </tr>
                <tr>
					<td style="font-weight:bold;">网址</td>
                    <td><input type="text" name="url"  id="url" class="validate[[ISREQUIRED],maxSize[1024],custom[url]] text_new" size="50" value="[URL]"/></td>
				</tr>
				<tr>
                    <td style="font-weight:bold">是否有抵扣联</td>
                    <td><input type="radio" name="deduction"  value="0"  [DEDUCTION1]/>&nbsp;无&nbsp;&nbsp;<input type="radio" name="deduction"  value="1" [DEDUCTION2]/>&nbsp;有</td>
                </tr>
                <tr>
					<td style="font-weight:bold;">发票税率</td>
                    <td>
                    	<input type="text" name="in_invoice_tax_rate" id=in_invoice_tax_rate style="width:35px;" class="validate[required,custom[number],max[100],min[0]] text_new" value="[INVOICETAXTRATE]"/>&nbsp;%
                    </td>    
				</tr>
				<tr>
                    <td style="font-weight:bold">供应商类型</td>
                    <td><input type="radio" name="supplier_type"  value="1" [SUPPLIERTYPE1]/>&nbsp;媒体&nbsp;&nbsp;<input type="radio" name="supplier_type"  value="2" [SUPPLIERTYPE2]/>&nbsp;外包</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">附件上传</td>
                    <td>
						<div>
                    		[DIDS]
						</div>
					</td>
                </tr>
                 <tr>
                	<td style="font-weight:bold;">实际供应商对应</td>
                	<td><select name="parentid" id="parentid" class="select">[PARENTSUPPLIERS]</select></td>
                </tr>
			</table>
            <div class="btn_div">
				<input type="hidden" name="id" id="id" value="[ID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="update_supplier"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
			</div>
			</form>
			<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
            <br />  
		</div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';

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

	[SUPPLIERJS]

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