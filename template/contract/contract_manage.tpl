<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 合同管理系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
    	<div class="crumbs">合同管理系统 - 客户合同管理</div>
    	<div class="tab" id="tab" style="height:30px">
    		<ul>
                <li class="on"><a>我的客户合同</a></li>
            </ul>
    	</div>
        <form id="formID" method="post" action="[BASE_URL]contract_cus/action.php" target="post_frame">
    	<div class="listform fix">
        	<table width="100%" class="tabin">
              <tr>
				<td>
                	&nbsp; 地区：&nbsp;<select id="city" name="city" class="select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="select">[DEPS]</select>&nbsp;<select id="team" name="team" class="select">[TEAMS]</select>&nbsp;
                    &nbsp; 开始时间：<input type="text" onclick="WdatePicker()" id="starttime" style=" width:80px;height:20px;" value="[STARTTIME]" />
                    &nbsp; 结束时间：<input type="text" onclick="WdatePicker()" id="endtime" style=" width:80px;height:20px;" value="[ENDTIME]" />
                	&nbsp; 关键字: <input id="search" style=" width:150px;height:20px;" value="[SEARCH]"  /> 
                    &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
                </td>
              </tr>
            </table>
        	<table class="etable" cellpadding="0" cellspacing="0" border="0" width="950">
            	<tr>
                  <th width="5%">&nbsp;</th>
                  <th width="10%">合同单号</th>
                  <th width="20%">合同名称</th>
                  <th width="20%">客户名称</th>
                  <th>可查阅人员</th>
                </tr>
                [CONTRACTLIST]
            </table>
            <div class="page_nav">
                <u>总记录：[ALLCOUNTS] 条 </u>
                <span class="page_nav_next">[NEXT]</span>
                <span class="page_nav_prev">[PREV]</span>
                <span class="page_nav_next">[COUNTS]</span>
            </div>
  		</div>

        <br/>
        <div class="listform fix">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                    <tr class="gradeB">
                        <th><b><span style="cursor:hand;">添加可查阅人员</span></b></th>
                    </tr>
                    <tr>
			<td height=10></td>
                    </tr>
                    <tr>
                        <td>
                          <select id="userlist" name="userlist" class="validate[required]">[USERLIST]</select>
                          &nbsp;&nbsp;
                          <input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="contract_manage"/><input type="submit" value="确 定" id="submit" />
                        </td>
                    </tr>
                </table>
            </div>
		</form>
		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]contract_cus/contract_cus.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
var base_url = '[BASE_URL]';
$(document).ready(function() {
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });

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
        getdtuserlist("contactperson",base_url,vcode);
    });
	
	$("#dosearch").live("click",function() { 
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
		var starttime = $("#starttime").val();
		var endtime = $("#endtime").val();
		var searchcontent = $("#search").val();
		location.href = $.sprintf(base_url + "contract_cus/?o=manage&starttime=%s&endtime=%s&city=%d&dep=%d&team=%d&search=%s",starttime,endtime,city,dep,team,searchcontent);
	});
	
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});
});
</script>
</body>
</html>
