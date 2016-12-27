<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 执行单外包详细信息</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/pop.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/default/easyui.css"/>
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/icon.css"/>
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">
		[TOP]
	</div>
	<div id="content" class="fix">
		<div class="crumbs">外包信息</div>
		<div class="tab">
			<ul>
				<li class="on"><a>执行单外包信息</a></li>
			</ul>
		</div>
        
		<div class="publicform fix">
			<table width="100%" cellpadding="0" cellspacing="0" border="1" class="sbd1">
                <tr>
                    <td style="font-weight:bold;width:150px;">外包名称</td>
                    <td>[SUPPLIERNAME]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">参与项目</td>
                    <td></td>
                </tr>
                <tr>
                	<td colspan="2">
                		<table  id="outsourcingdg"></table>
                	</td>
                </tr>
			</table>
		</div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script src="[BASE_URL]script/ajaxfileupload.js"></script>
<script src="[BASE_URL]script/My97DatePicker/WdatePicker.js"></script>
<script src="[BASE_URL]script/jquery.autocomplete.min.js"></script>
<script src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]js/upload.js"></script>
<script src="[BASE_URL]outsourcing/outsourcing.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var vcode = '[VCODE]';
var base_url = '[BASE_URL]';

$(document).ready(function() {
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true){
			$("#pcidlist").next("div").show().html($(this).next('span').text());
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

	$("#outsourcingdg").datagrid({
		url:base_url + "get_data.php?action=getOutsourcing&id=[ID]",
		height:"auto",
		title:"已参与项目列表",
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		columns:[[
		 {field:'a',width:100,title:"执行单号"},
         {field:'b',width:200,title:"项目名称"},
          {field:'c',width:200,title:"成本金额"}
          ]]
	});
});
</script>
</body>
</html>
