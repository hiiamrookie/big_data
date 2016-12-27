//ajax 返回错误的信息 处理 （待完善后上传至服务器）
function doerror(XHR,status,data)
{
  alert(status+" : "+data);
}

/**
 * 根据分公司获取分公司的部门列表
 * @param obj	分公司select对象
 * @param base_url 网站base_url
 * @param vcode	 验证code
 */
function getdepsbycity(obj,base_url,vcode){
	$.ajax({
		   type: "post",
		   url: base_url + "get.php",
		   cache:"false",
		   data: "action=get_deps_by_city&cityid=" + obj.value + "&t=" + Math.random() + "&vcode=" + vcode,
		   dataType:'text',
		   async: false,
		   success: function(msg){
			  $("#dep").html('<option value="">请选择部门</option>' + msg);
		   },
	 	   error: function(e){
	 		   alert("获取部门数据异常");
	 	   }
	});
}

/**
 * 根据部门获取团队列表
 * @param obj	部门select对象
 * @param base_url 网站base_url
 * @param vcode	 验证code
 */
function getteamsbydep(obj,base_url,vcode){
	$.ajax({
		   type: "post",
		   url: base_url + "get.php",
		   cache:"false",
		   data: "action=get_teams_by_dep&depid=" + obj.value + "&t=" + Math.random() + "&vcode=" + vcode,
		   dataType:'text',
		   async: false,
		   success: function(msg){
			  $("#team").html('<option value="">请选择团队</option>' + msg);
		   },
	 	   error: function(e){
	 		   alert("获取团队数据异常");
	 	   }
	});
}

function getdtuserlist(id,base_url,vcode){
	var depid=$("#dep").val();
	var teamid=$("#team").val();

	$.ajax({
		   type: "post",
		   url: base_url + "get.php",
		   cache:"false",
		   data: "action=get_user_by_dep_team&depid=" + depid + "&teamid=" + teamid + "&t=" + Math.random() + "&vcode=" + vcode,
		   dataType:'text',
		   async: false,
		   success: function(msg){
			   id = id.split(",");
			   for(var i=0;i<id.length;i++){
				   $("#"+id[i]).empty();
				   $("#"+id[i]).append(msg); 
			   }	   
		   },
	 	   error: function(e){
	 		   alert("获取用户数据异常");
	 	   }
	});
}