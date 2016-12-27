<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 填写拆月数据</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/pop.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/default/easyui.css"/>
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/icon.css"/>
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">
		[TOP]
	</div>
	<div id="content" class="fix">
		<div class="crumbs">执行单管理系统 - 填写执行单 [PID] 拆月数据</div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a>执行单详情</a></li>
				<li><a>流转状态</a></li>
			</ul>
		</div>
		<div class="box">
		<form id="formID" method="post" action="[BASE_URL]executive/action.php" target="post_frame">
            <div class="publicform fix">
            	[PIDINFO]
                <div class="btn_div">
                	<input type="hidden" name="vcode" value="[VCODE]"/>
                	<input type="hidden" name="pid" value="[PID]"/>
                	<input type="hidden" name="executive_id" value="[EXEID]"/>
                	<input type="hidden" name="action" value="executive_complete_cy"/>
                    <input id="confirm" type="submit" value="提交" class="btn_sub"/>
                </div>        
        	</div>
        	</form>
        		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
        <div class="box">
            <div class="listform" style="padding:0">
               [LOGLIST]
            </div>
        </div> 
	</div>
</div>
<form id="cyform" method="post" action="[BASE_URL]executive/action.php"  target="post_frame">
<div class="overlay">
	[CYOVERLAY]
</div>
</form>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.autocomplete.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]executive/executive.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var cy_on = [CYON];
var vcode = '[VCODE]';
$(document).ready(function() {
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

	$("#tab li").click(function(){
		$(this).addClass("on").siblings("li").removeClass();
		$(".box:eq("+$(this).index()+")").show().siblings(".box").hide();
	}).eq(0).click();

	$("#addcostpayment").click(addcostpayment);
	$("#addcybtn").click(addcy);
});

var suppliers = [SUPPLIERS];

function getSupplierName(obj){
	$(obj).autocomplete(base_url + "finance/supplier/?o=getSupplierName", { width: 300, max: 50 });
}
</script>
</body>
</html>
