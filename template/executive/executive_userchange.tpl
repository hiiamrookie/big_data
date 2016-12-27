<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 执行单系统</title>
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
		<div class="crumbs">执行单管理系统 - 执行单管理</div>
		<div class="tab" id="tab" style="height:30px">
        	<ul>
			<li><a href="?o=manage">执行单管理</a></li>
            <li class="on"><a>执行单人员变更</a></li>
			<li><a href="?o=tj">执行单统计</a></li>
		</ul>
		</div>
        <div class="listform fix">
        	<form id="formID" method="post" action="[BASE_URL]executive/action.php" target="post_frame">
        	<table width="100%" class="tabin">
              <tr>
				<td>
					&nbsp; <input type="radio" name="seltype" value="0" checked="checked"/>&nbsp;单个执行单
                	&nbsp;<input type="radio" name="seltype" value="1"/>&nbsp;所有执行单
                </td>
              </tr>
            </table>
            <table class="etable sbd1" cellpadding="0" cellspacing="0" border="0" id="table0">
            	<tr>
                	<td width="150"><b>搜索执行单号</b></td>
                    <td style="text-align:left"> <input id="search" name="search" style=" width:150px" value="[SEARCH]"  />&nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/></td>
                </tr>
                <tr>
                	<td width="150"><b>执行单号</b></td>
                    <td style="text-align:left"><div id="pid">[PID]<input type="hidden" name="pid" value="[PID]"/></div></td>
                </tr>
                <tr>
                	<td><b>执行单名称</b></td>
                    <td style="text-align:left"><div id="name">[NAME]</div></td>
                </tr>
                <tr>
                	<td><b>发起人</b></td>
                    <td style="text-align:left">[USERS]</td>
                </tr>
                <tr>
                	<td><b>项目负责人</b></td>
                    <td style="text-align:left">[PRINCIPAL]</td>
                </tr>
                <tr>
                	<td></td>
                    <td style="text-align:left">
                    <input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="executive_userchange"/><input type="button" value="&nbsp;确认修改&nbsp;" class="longbtn" id="submit1" />
                    </td>
                </tr>
            </table>
            <table class="etable sbd1" cellpadding="0" cellspacing="0" border="0" id="table1">
            	<tr>
            		<td width="150"><b>所属地区/部门/团队</b></td>
            		<td style="text-align:left">
                        <select id="city" name="city" class="select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="select"><option value="">请选择部门</option></select>&nbsp;<select id="team" name="team" class="select"><option value="">请选择团队</option></select>
                	</td>
            	</tr>
            	 <tr>
                	<td><b>原来人员</b></td>
                    <td style="text-align:left"><select id="type1users" name="type1users" class="select"><option value="">请选择人员</option></select></td>
                </tr>
                <tr>
                	<td><b>变更人员</b></td>
                    <td style="text-align:left"><select id="type1principal" name="type1principal" class="select"><option value="">请选择人员</option></select></td>
                </tr>
                <tr>
                	<td></td>
                	<td style="text-align:left"><input type="button" value="&nbsp;确认修改&nbsp;" class="longbtn" id="submit2" /></td>
                </tr>
            </table>
            </form>
            <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]executive/executive.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {		
	$("#dosearch").live("click",function () { 
		var search = js_escape($("#search").val());
		location.href = base_url + "executive/?o=userchange&search=" + search;
	});

	$("#table1").hide();
	
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

	$('input[name="seltype"]').click(function(){
		var v = $(this).val();
		var o = 1 - Number(v);
		$("#table" + v).show();
		$("#table" + o).hide();
	});

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
        getdtuserlist("type1users,type1principal",base_url,vcode);
    });

    //团队选择队员
    $("#team").live("change",function(){ 
        getdtuserlist("type1users,type1principal",base_url,vcode);
    });

    $("#submit1").click(function(){
    	$("#action").val("executive_userchange");
    	$("#formID").submit();
    });
    
    $("#submit2").click(function(){
		$("#action").val("executive_userchange_all");
		$("#formID").submit();
     });
});
</script>
</body>
</html>
