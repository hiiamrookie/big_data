<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> API设定</title>
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
		<div class="crumbs">API管理</div>
		<div class="tab">
			<ul>
            	<li class="on"><a>API验证列表</a></li>
				<li><a href="[BASE_URL]system/?o=api_auth_add">API验证添加</a></li>
			</ul>
		</div>
        <div class="listform fix">
			<table class="etable" cellpadding="0" cellspacing="0" border="0">
				<tr>
                    <th width="10%">编号</th>
					<th width="20%">API验证名称</th>
					<th width="20%">API验证代码</th>
                    <th width="20%">API验证类型</th>
                    <th width="10%">状态</th>
					<th width="20%">操作</th>
				</tr>
                [API_AUTH_LIST]
			</table>
		</div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]system/system.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
function del(id){
	if(window.confirm("确定要删除该API验证信息吗？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=del_api_auth&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   alert(msg);
			   },
		 	   error: function(e){
		 		   alert("删除数据异常");
		 	   }
		});
		location.href="[BASE_URL]system/?o=api_auth_list";
	}
}
</script>
</body>
</html>
