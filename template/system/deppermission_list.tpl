<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 系统设置</title>
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
		<div class="crumbs">系统设置 - 部门内权限编辑</div>
		<div class="tab">
			<ul>
            	<li class="on"><a href="?o=deppermissionlist">部门内部权限列表</a></li>
				<li><a href="?o=deppermissionadd">部门内部权限添加</a></li>
			</ul>
		</div>
        <div class="listform fix">
			<table class="etable" cellpadding="0" cellspacing="0" border="0">
				<tr>
                    <th width="10%">编号</th>
                    <th width="20%">部门</th>
					<th width="20%">名称</th>
					<th width="20%">描述</th>
					<th width="30%">操作</th>
				</tr>
                [DEPPERMISSION_LIST]
			</table>
		</div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]system/system.js"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
function del(id){
	if(window.confirm("确定要删除该部门角色权限吗？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=del_deppermission&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   alert(msg);
			   },
		 	   error: function(e){
		 		   alert("删除数据异常");
		 	   }
		});
		location.href="[BASE_URL]system/?o=deppermissionlist";
	}
}
</script>
</body>
</html>
