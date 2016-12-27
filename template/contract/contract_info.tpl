<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 合同管理系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">客户合同管理系统 - 查阅客户合同</div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a href="#"><span style=" font-weight:bold">[CID]</span></a></li>
                <li><a>流转状态</a></li>
			</ul>
		</div>
        <div class="box">
            <div class="publicform fix">
                [INFO]
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
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]contract_cus/contract_cus.js" language="javascript"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#tab li").click(function(){
		$(this).addClass("on").siblings("li").removeClass();
		$(".box:eq("+$(this).index()+")").show().siblings(".box").hide();
	}).eq(0).click();
});
</script>
</body>
</html>
