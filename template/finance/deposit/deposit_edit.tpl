<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理</title>
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
    	<div class="crumbs">财务管理 - 保证金修改</div>
    	<div class="tab">
      		<ul>
        		<li class="on"><a>保证金修改</a></li>
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
		          <td><input type="text" class="validate[required,custom[money]] ac_input text_new"  style="width:300px;" id="amount" name="amount" value="[AMOUNT]"/> 元</td>
		        </tr>
      		</table>
      		<br />
	      	<div id="audit1"  style="display:[SHOWSTATUS]">
	      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
		        	<tr class="gradeB">
		          		<th><b><span style="cursor:hand;">审核意见</span></b></th>
		        	</tr>
			        <tr>
			        	<td style="font-weight:bold;color:red;">
			            	[AUDITMSG]
			            </td>
			        </tr>
	      		</table>
	      	</div>
	      	<div class="btn_div">
		      		<input type="hidden" name="depositid" id="depositid" value="[ID]"/>
		        	<input id="confirm1" type="submit" value="提交" class="btn_sub" />
		        	<input type="hidden" name="vcode" id="vcode" value="[VCODE]"/>
		        	<input type="hidden" name="action" id="action" value="deposit_edit"/>
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
});
</script>
</body>
</html>
