<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 合同管理系统</title>
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
    	<div class="crumbs">合同管理系统 - 客户合同查阅</div>
    	<div class="tab" id="tab" style="height:30px">
    		<ul>
				<li class="on"><a>客户合同查阅</a></li>
			</ul>
    	</div>
    	<div class="listform fix">
        	<table width="100%" class="tabin">
              <tr>
				<td>
                	&nbsp; 地区：&nbsp;<select id="city" name="city" class="select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="select">[DEPS]</select>&nbsp;<select id="team" name="team" class="select">[TEAMS]</select>&nbsp;
                    &nbsp; 开始时间：<input type="text" onfocus="WdatePicker()" id="starttime" style=" width:80px;height:20px;" value="[STARTTIME]" />
                    &nbsp; 结束时间：<input type="text" onfocus="WdatePicker()" id="endtime" style=" width:80px;height:20px;" value="[ENDTIME]" />
                	&nbsp; 关键字: <input id="search" style=" width:150px;height:20px;" value="[SEARCH]"  /> 
                    &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
                </td>
              </tr>
            </table>
            
        	<table class="etable" cellpadding="0" cellspacing="0" border="0" width="950" id="clist">
        		<thead>
            	<tr>
               	    <th width="5%">类型</th>
                    <th width="15%"  style="text-align:left">合同单号 &nbsp;【所属框架】</th>
                    <th width="15%">项目内容</th>
                    <th width="15%">客户名称</th>
                    <th width="10%">合同金额</th>
                    <th width="15%">审核状态</th>
                    <th width="5%">耗时</th>
                    <th width="5%">归档</th>
                    <th width="10%">提交时间</th>
                    <th width="5%">操作</th>
                </tr>
                </thead>
                <tbody>
                [CONTRACTLIST]
                </tbody>
            </table>
            <div class="page_nav">
                <u>总记录：[ALLCOUNTS] 条 </u>
                <span class="page_nav_next">[NEXT]</span>
                <span class="page_nav_prev">[PREV]</span>
                <span class="page_nav_next">[COUNTS]</span>
            </div>
  		</div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]contract_cus/contract_cus.js" language="javascript"></script>
<script type="text/javascript">
var vcode = "[VCODE]";
var base_url = '[BASE_URL]';
$(document).ready(function() {
	$.tablesorter.addParser({
		id: "day", //指定一个唯一的ID
		is: function(s){
		   return false;
		},
		format: function(s){
		   return s.toLowerCase().replace(/天/,0); //将中文换成数字
		},
		type: "numeric" //按数值排序
	});

	$.tablesorter.addParser({
		id: "cu", //指定一个唯一的ID
		is: function(s){
		   return false;
		},
		format: function(s){
		   return s.toLowerCase().replace(/￥/,"").replace(/,/g,"");
		},
		type: "numeric" //按数值排序
	});
	$("#clist").tablesorter({
		headers:{
			4:{sorter:"cu"},
			6:{sorter:"day"}
		}
	});
	
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
	
	$("#dosearch").live("click",function () { 
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
			
		location.href = $.sprintf(base_url + "contract_cus/?o=list&starttime=%s&endtime=%s&city=%d&dep=%d&team=%d&search=%s",starttime,endtime,city,dep,team,searchcontent);
	});
	
	$("#download").live("click",function() { 
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
		location.href = $.sprintf(base_url + "contract_cus/?o=download&city=%d&dep=%d&team=%d",city,dep,team);
	});
});

function cancelcid(cid){
	if(window.confirm("确定要撤销吗？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=cancel_contract&cid=" + cid + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				  alert(msg);
			   },
		 	   error: function(e){
		 		   alert("撤销异常");
		 	   }
		});
		location.href = base_url + "contract_cus/?o=list";
	}
}

</script>
</body>
</html>
