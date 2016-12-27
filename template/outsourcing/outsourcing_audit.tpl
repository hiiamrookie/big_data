<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 审核执行单外包信息</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/pop.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">
		[TOP]
	</div>
	<div id="content" class="fix">
		<div class="crumbs">外包审核管理</div>
		<div class="tab">
			<ul>
				<li class="on"><a>审核执行单外包信息</a></li>
			</ul>
		</div>
        
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]outsourcing/action.php" target="post_frame">
			<table width="100%" cellpadding="0" cellspacing="0" border="1" class="sbd1">
                <tr>
                    <td style="font-weight:bold;width:150px;">执行单号</td>
                    <td><a href="[BASE_URL]executive/?o=info&id=[ID]&pid=[PID]" target="_blank">[PID]</a></td>
                </tr>
                <tr>
                	<td style="font-weight:bold">项目名称</td>
                	<td>[NAME]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">外包成本</td>
                    <td>[SUMCOST]</td>
                </tr>
			</table>
			<p/>
			<table width="100%" cellpadding="0" cellspacing="0" border="1" class="sbd1">
				<tr>
					<td style="font-weight:bold;width:300px;">支持部门名称</td>
					<td style="font-weight:bold">外包名称</td>
					<td style="font-weight:bold">外包成本</td>
				</tr>
				[OUTSOURCINGLIST]
			</table>
			<p/>
			<table width="100%" cellpadding="0" cellspacing="0" border="1" class="sbd1">
				<tr>
					<td style="font-weight:bold;width:150px;">审核留言</td>
					<td><textarea style="width:400px;height:84px" rows="3" name="remark" id="remark" class="validate[optional,maxSize[500]] textarea"></textarea></td>
				</tr>
			</table>
			<div class="btn_div">
				<input type="hidden" name="vcode" value="[VCODE]"/>
				<input type="text" name="id" value="[ID]"/>
				<input type="text" name="pid" value="[PID]"/>
				<input type="text" name="executive_dep_array" value='[EXECUTIVEDEP]'/>
				<input type="hidden" name="action" value="audit_outsourcing"/>
				<input type="text" name="audit_pass" id="audit_pass" value="0"/>
				<input id="confirm" type="button" value="审核确认" class="btn_qr"/>
                <input id="reject" type="button" value="审核驳回" class="btn_bh" />
			</div>
			</form>
			<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
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

	$("#confirm").click(function(){ 
		$("#remark").removeClass("validate[required,maxSize[500]]");
		$("#remark").addClass("validate[optional,maxSize[500]]");
		if(window.confirm("您确定要审核确认吗？")){
			$("#audit_pass").val("1");
			$("#formID").submit();
		}
	});
	
	$("#reject").click(function(){ 
		$("#remark").removeClass("validate[optional,maxSize[500]]");
		$("#remark").addClass("validate[required,maxSize[500]]");
		if(window.confirm("您确定要审核驳回吗？")){
			$("#audit_pass").val("0");
			$("#formID").submit();
		}
	});
});
</script>
</body>
</html>
