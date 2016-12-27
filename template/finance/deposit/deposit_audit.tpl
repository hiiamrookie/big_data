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
    	<div class="crumbs">财务管理 - 保证金审核</div>
    	<div class="tab">
      		<ul>
        		<li class="on"><a>保证金审核</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
		        <tr>
		          <td style="font-weight:bold;width:150px">申请时间</td>
		          <td>[TIME]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold;width:150px">所属合同</td>
		          <td>[CID]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold;width:150px">客户名称</td>
		          <td>[CUSNAME]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold;width:150px">保证金金额</td>
		          <td><font color="#ff9933"><b>[AMOUNT]</b></font> 元</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold">申请人</td>
		          <td>[USERINFO] </td>
		        </tr>
      		</table>
      		<br />
	      	<div id="audit1" >
	      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
		        	<tr class="gradeB">
		          		<th><b><span style="cursor:hand;">审核意见</span></b></th>
		        	</tr>
			        <tr>
			        	<td style="font-weight:bold">
			            	<textarea style="width:400px;height:84px" name="auditmsg" id="auditmsg" class="validate[optional,maxSize[500]] textarea" rows="3"></textarea>
			            </td>
			        </tr>
	      		</table>
		      	<div class="btn_div">
		      		<input type="hidden" name="depositid" id="depositid" value="[ID]"/>
		        	<input id="confirm1" type="button" value="审核通过" class="btn_qr" />
		        	<input id="reject" type="button" value="审核驳回" class="btn_bh" />
		      	</div>
	      	</div>
      		<div>
      			<input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="deposit_leaader_audit_pass"/>
      		</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
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

	//确认
	$("#confirm1").click(function(){
		$("#auditmsg").removeClass("validate[required,maxSize[500]]");
		$("#auditmsg").addClass("validate[optional,maxSize[500]]");
		$("#action").val("deposit_leaader_audit_pass");
		$("#formID").submit();
	});
	
	//审核驳回
	$("#reject").click(function(){
		$("#auditmsg").removeClass("validate[optional,maxSize[500]]");
		$("#auditmsg").addClass("validate[required,maxSize[500]]");
		$("#action").val("deposit_leaader_audit_reject");
		$("#formID").submit();
	});
});
</script>
</body>
</html>
