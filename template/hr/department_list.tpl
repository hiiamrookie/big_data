<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 人事管理系统</title>
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
		<div class="crumbs">人事管理系统</div>
		<div class="tab" id="tab">
			<ul>
                <li class="on"><a>部门列表</a></li>
                <li><a href="?o=adddepartment">新增部门</a></li>
			</ul>
		</div>
        <div class="listform fix">
            <table class="etable" cellpadding="0" cellspacing="0" border="0">
                <tr>
                	<th>编号</th>
                    <th>公司名称</th>
                    <th>部门名称</th>
                    <th>是否支持部门</th>
                    <th>操作</th>
                </tr>
                [DEPLIST]
            </table>
        </div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]script/jquery.sprintf.js"></script>
<script src="[BASE_URL]hr/hr.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
$(document).ready(function() {
	$(".etable tr:odd").addClass("bw");
});

function del(id){
	if(window.confirm("确定要删除该部门吗？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=del_dep&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   alert(msg);
			   },
		 	   error: function(e){
		 		   alert("删除数据异常");
		 	   }
		});
		location.href="[BASE_URL]hr/?o=departmentlist";
	} 
}
</script>
</body>
</html>
