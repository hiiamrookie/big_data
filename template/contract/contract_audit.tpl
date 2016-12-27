<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 合同管理系统</title>
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
	<div class="nav_top">
		[TOP]
	</div>
	<div id="content" class="fix">
		<div class="crumbs">合同管理系统 - 审核客户合同 </div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a href="#"><span style=" font-weight:bold">[CID]</span></a></li>
                <li><a>流转状态</a></li>
			</ul>
		</div>
        <div class="box">
            <div class="publicform fix">
                 [INFO]
                <br />
                <form id="formID" method="post" action="[BASE_URL]contract_cus/action.php" target="post_frame">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                    <tr class="gradeB">
                        <th><b><span style="cursor:hand;">审核意见与操作</span></b></th>
                    </tr>
                    <tr>
                        <td><textarea style="width:450px;height:64px" id="remark" class="textarea" rows="4" name="remark"></textarea></td>
                    </tr>
                    <tr>
                    	<td>
                        	<div class="btn_div">
                        		<input type="hidden" name="vcode" value="[VCODE]"/>
                				<input type="hidden" name="cid" value="[CID]"/>
			                	<input type="hidden" name="action" value="contract_audit"/>
			                	<input type="hidden" name="audit_pass" id="audit_pass" value="0"/>
                                <input id="confirm" type="button" value="审核通过" class="btn_qr" />
                                <input id="reject" type="button" value="审核驳回" class="btn_bh" />
                            </div>
                        </td>
                    </tr>
            	</table>
            	</form>
            	<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
            </div>
        </div>
        <div class="box">
            <div class="listform" style="padding:0">
               [LOGLIST]
            </div>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]contract_cus/contract_cus.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
//var ID="[ID]";
//var CID="[CID]";
$(document).ready(function() {
	$("#tab li").click(function(){
		$(this).addClass("on").siblings("li").removeClass();
		$(".box:eq("+$(this).index()+")").show().siblings(".box").hide();
	}).eq(0).click();

	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});
	
	$("#confirm").click(function(){ 
		$("#remark").removeClass("validate[required]");
		if(window.confirm("您确定要审核确认吗？")){
			$("#audit_pass").val("1");
			$("#formID").submit();
		}
	});
	
	$("#reject").click(function(){ 
		$("#remark").addClass("validate[required]");
		if(window.confirm("您确定要审核驳回吗？")){
			$("#audit_pass").val("0");
			$("#formID").submit();
		}
	});
});
</script>
</body>
</html>
