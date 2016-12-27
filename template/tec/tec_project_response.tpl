<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 响应项目需求</title>
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
	<div class="nav_top">
		[TOP]
	</div>

	<div id="content" class="fix">
		<div class="crumbs">项目需求管理</div>
		<div class="tab">
			<ul>
				<li class="on"><a>响应项目需求</a></li>
				<li><a href="[BASE_URL]tec/?o=projectlist">项目需求列表</a></li>
			</ul>
		</div> 
		<div class="publicform fix">
			[PROJECTINFO]
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]js/upload.js"></script>
<script src="[BASE_URL]tec/tec.js"></script>
<script src="[BASE_URL]js/common.js"></script>
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
	
	checkdifferent();

	$("input[name^='response_']").click(function(){
		var name = $(this).attr("name");
		name = name.split("_");
		var remarkid = 'remark_' + name[1];
		if($(this).val() == "2"){
			//remark必须输入
			$("#" + remarkid).removeClass("validate[optional,maxSize[1000]] text").addClass("validate[required,maxSize[1000]] text");
		}else{
			//remark可以输入
			$("#" + remarkid).removeClass("validate[required,maxSize[1000]] text").addClass("validate[optional,maxSize[1000]] text");
		}
	});  
});
</script>
</body>
</html>