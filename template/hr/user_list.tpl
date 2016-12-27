<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 账户列表</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/tablesorter.css" rel="stylesheet" media="screen" type="text/css" />
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
                <li class="on"><a>账户列表</a></li>
                <li><a href="?o=adduser">账户添加</a></li>
			</ul>
		</div>
        <div class="listform fix">
        	<table width="100%" class="tabin">
                <tr>
                    <td >
                        &nbsp; 地区：&nbsp;<select id="city" name="city" class="select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="select">[DEPS]</select>&nbsp;<select id="team" name="team" class="select">[TEAMS]</select>&nbsp;
                        <input type="button" value="确 定" id="refresh" class="btn"/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input id="search" style="width:300px; height:18px;" /> <input type="button" id="dosearch" value="搜 索" class="btn"/>
                    </td>
                </tr>
            </table>
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="ulist">
            	<thead>
                <tr>
                	<th width="5%">编号</th>
                    <th width="15%">地区\部门\团队</th>
                    <th width="10%">账户名</th>
                    <th width="10%">真实姓名</th>
                    <th width="5%">性别</th>
                    <th width="15%">邮箱</th>
                    <th width="10%">手机号</th>
                    <th width="10%">在职状态</th>
                    <th width="20%">操作</th>
                </tr>
                </thead>
                <tbody>
                [USERLIST]
                </tbody>
            </table>
        </div>
        <div class="page_nav">
            <u>总记录：[ALLUSERCOUNT] 条 </u>

            <span class="page_nav_next">[NEXT]</span>
            <span class="page_nav_prev">[PREV]</span>
        </div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.tablesorter.js"></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]script/jquery.sprintf.js"></script>
<script src="[BASE_URL]hr/hr.js"></script>
<script type="text/javascript">
var PAGE="[PAGE]";
var vcode = "[VCODE]";
var base_url = '[BASE_URL]';
$(document).ready(function() {
	$("#ulist").tablesorter();
	
	$(".etable tr:odd").addClass("bw");
	
	//从地区选择部门
    $("#city").live("change",function(){ 
        $("#dep").html('<option value="">请选择部门</option>');
        $("#team").html('<option value="">请选择团队</option>');
        getdepsbycity(this,base_url,vcode);
    });
    
    //从部门选择团队
    $("#dep").live("change",function(){ 
    	$("#team").html('<option value="">请选择团队</option>');
        getteamsbydep(this,base_url,vcode); 
    });
	
	$("#refresh").live("click",function(){
		var city = $("#city").val();
		if(city == ""){
			city = 0;
		}
		var dep = $("#dep").val();
		if(dep == ""){
			dep = 0;
		}
		var team = $("#team").val();
		if(team == ""){
			team = 0;
		}
		location.href = base_url + "hr/?o=userlist&city=" + city + "&dep=" + dep + "&team=" + team;
	});
	
	$("#dosearch").live("click",function () { 
		location.href = base_url + "hr/?o=userlist&search=" + $("#search").val();
	});
});

function canceluser(uid){
	if(window.confirm("确定要注销该用户吗？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=cancel_user&uid=" + uid + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				  alert(msg);
			   },
		 	   error: function(e){
		 		   alert("注销用户异常");
		 	   }
		});
		location.href="[BASE_URL]hr/?o=userlist";
	}
}

function changepwd(uid){
	if(window.confirm("确定要重置该用户的密码吗？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=change_pwd&uid=" + uid + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				  alert(msg);
			   },
		 	   error: function(e){
		 		   alert("重置用户密码异常");
		 	   }
		});
		location.href="[BASE_URL]hr/?o=userlist";
	}
}
</script>
</body>
</html>
