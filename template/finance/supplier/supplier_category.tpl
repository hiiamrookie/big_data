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
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
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
        		<li><a href="?o=supplierimport">供应商信息导入</a></li>
        		<li><a href="?o=supplierindustry">新建客户行业分类</a></li>
        		<li><a href="?o=supplierindustrylist">客户行业分类列表</a></li>
        		<li class="on"><a>新建供应商产品分类</a></li>
        		<li><a href="?o=suppliercategorylist">供应商产品分类列表</a></li>
        		<li><a href="?o=supplier_export">信息导出</a></li>
			</ul>
		</div>
        <div class="publicform fix">
         <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="font-weight:bold;width:150px">供应商名称</td>
                    <td><input type="text" name="supplier_name" id="supplier_name" class="validate[required,maxSize[255]] text_new" style="width:300px;"/></td>
                </tr>
                 <tr>
                    <td style="font-weight:bold;width:150px">产品分类名称</td>
                    <td><input type="text" name="category_name"  id="category_name" class="validate[required,maxSize[255]] text_new" style="width:300px;"/></td>
                </tr>
                 <!--tr>
                    <td style="font-weight:bold;width:150px">返点比例</td>
                    <td><input type="text" name="rebate"  id="rebate" class="validate[required,custom[number],min[0],max[100]] text_new" style="width: 40px;"/>&nbsp;%</td>
                </tr>
                <tr>
                	<td style="font-weight:bold;width:150px">返点比例适用时间段</td>
                	<td>
                		开始时间：&nbsp;<input type="text" id="starttime" name="starttime" onclick="WdatePicker();" class="validate[required] text Wdate" readonly="readonly"/>&nbsp;&nbsp;
                		结束时间：&nbsp;<input type="text" id="endtime" name="endtime" onclick="WdatePicker({minDate:'#F{$dp.$D(\'starttime\')}'})" class="validate[required] text Wdate" readonly="readonly"/>
                	</td>
                </tr-->
            </table>
            <div class="btn_div"><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="supplier_category"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
			</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.autocomplete.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
$(document).ready(function() {
	//查询已有的媒体名称
	$("#supplier_name").autocomplete(base_url + "finance/supplier/?o=getSupplierName", { width: 300, max: 50 });
	
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
