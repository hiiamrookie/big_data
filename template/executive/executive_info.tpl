<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 审核执行单</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
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
		<div class="crumbs">执行单管理系统 -- 查阅执行单 [PID]</div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a>执行单详情</a></li>
				<li><a>流转状态</a></li>
                <li><a href="[BASE_URL]executive/?o=print&id=[ID]&pid=[PID]&d=[D]" target="_blank">打印</a></li>
			</ul>
		</div>
		<div class="box">
            <div class="publicform fix">
            	[PIDINFO]
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
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]executive/executive.js" language="javascript"></script>
<script type="text/javascript">
var pid="[PID]";
var cy_on = [CYON];
$(document).ready(function() {
	$("#tab li").click(function(){
		$(this).addClass("on").siblings("li").removeClass();
		$(".box:eq("+$(this).index()+")").show().siblings(".box").hide();
	}).eq(0).click();
	[CHECKDIFFERENT]

	if(cy_on){
		$("#amount_cy_tr").show();
		$("#cost_cy_tr").show();
		$("#support_cy_tr").show();
	}else{
		$("#amount_cy_tr").hide();
		$("#cost_cy_tr").hide();
		$("#support_cy_tr").hide();
	}
});

</script>
</body>
</html>
