<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 模块流程设定</title>
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
		<div class="crumbs">流程管理系统 - 流程编辑</div>
		<div class="tab">
			<ul>
            	<li class="on"><a href="[BASE_URL]manage/?o=processlist">流程列表</a></li>
				<li><a href="[BASE_URL]manage/?o=processadd">流程添加</a></li>
			</ul>
		</div>
        <div class="listform fix">
			<table class="etable" cellpadding="0" cellspacing="0" border="0">
				<tr>
                    <th width="10%">编号</th>
					<th width="20%">流程名称</th>
					<th width="20%">描述</th>
                    <th width="30%">应用部门</th>
					<th width="20%">操作</th>
				</tr>
                [PROCESS_LIST]
			</table>
		</div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]manage/manage.js" language="javascript"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
function del(id){
	if(window.confirm("确定要删除该流程吗？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=del_process&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   alert(msg);
			   },
		 	   error: function(e){
		 		   alert("删除数据异常");
		 	   }
		});
		location.href="[BASE_URL]manage/?o=processlist";
	}
}
</script>
</body>
</html>
