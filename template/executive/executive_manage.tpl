<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 执行单系统</title>
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
		<div class="crumbs">执行单管理系统 - 执行单管理</div>
		<div class="tab" id="tab" style="height:30px">
        	<ul>
				<li class="on"><a>执行单管理</a></li>
				<li><a href="?o=userchange">执行单人员变更</a></li>
				<li><a href="?o=tj">执行单统计</a></li>
			</ul>
		</div>
        
        <div class="listform fix">
        	<table width="100%" class="tabin">
              <tr>
				<td>
                	&nbsp; <input type="checkbox" id="isgd" [CHECK] /> 未归档
                	&nbsp; 地区：&nbsp;<select id="city" name="city" class="select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="select">[DEPS]</select>&nbsp;<select id="team" name="team" class="select">[TEAMS]</select>&nbsp;
                    &nbsp; 开始时间：<input type="text" onfocus="WdatePicker()" id="starttime" style=" width:80px;height:20px;" value="[STARTTIME]" />
                    &nbsp; 结束时间：<input type="text" onfocus="WdatePicker()" id="endtime" style=" width:80px;height:20px;" value="[ENDTIME]" />
                	&nbsp; 关键字: <input id="search" style=" width:150px;height:20px;" value="[SEARCH]"  /> 
                    &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
                </td>
              </tr>
            </table>
            
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="example">
              <thead>
                <tr>
                	<th width="5%">类型</th>
                    <th width="10%">单号</th>
                    <th width="15%">客户名称</th>
                    <th width="15%">项目名称</th>
                    <th width="10%">总金额</th>
                    <th width="10%">总成本<font color="#0000FF">（预估）</font></th>
                    <th width="10%">状态</th>
                    <th width="5%">耗时</th>
                    <th width="10%">创建时间</th>
                    <th width="10%">操作</th>
                </tr>
              </thead>
              <tbody>
              	[MANAGEEXECUTIVELIST]
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
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]executive/executive.js" language="javascript"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
var vcode = "[VCODE]";
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
	
	$("#example").tablesorter({
		headers:{
			4:{sorter:"cu"},
			5:{sorter:"cu"},
			7:{sorter:"day"}
		}
	});
	
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });
	
	//绑定从地区选择部门
    $("#city").live("change",function(){ 
        getdepsbycity(this,base_url,vcode); 
    });
    
    //绑定从部门选择团队
    $("#dep").live("change",function(){ 
        getteamsbydep(this,base_url,vcode); 
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
		var starttime=$("#starttime").val();
		var endtime=$("#endtime").val();
		var searchcontent=$("#search").val();
		var gd=0;
		if ($("#isgd").attr("checked")=="checked"){
			gd=1;
		} 
			
		location.href=$.sprintf(base_url + "executive/?o=manage&starttime=%s&endtime=%s&city=%d&dep=%d&team=%d&search=%s&gd=%d",starttime,endtime,city,dep,team,searchcontent,gd);
	});
	
});


function gd(id,v){
	if(window.confirm("确认归档？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:false,
			   data: "action=gd&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				  //alert(msg);
				  //$(v).parent().html("<font color='#66cc00'>已归档</font>");
				  if(msg == "1"){
						alert("归档成功");
						$(v).parent().html("<font color='#66cc00'>已归档</font>");
				  }else{
					  alert(msg);
				  }
			   },
		 	   error: function(e){
		 		   alert("归档错误，请重试！！");
		 	   }
		});
	}
}

function cancel(id,v){
	if(window.confirm("确认撤销？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:false,
			   data: "action=cancel&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				 // alert(msg);
				 // $(v).parent().parent().parent().children().eq(6).html("<font color='red'><b>已撤销</b></font>");
			     // $(v).parent().html("");
			      if(msg=="1"){
					  alert("撤消成功");
					  $(v).parent().parent().parent().children().eq(6).html("<font color='red'><b>已撤销</b></font>");
				      $(v).parent().html("");
				   }else{
					   alert(msg);
					}
			   },
		 	   error: function(e){
		 		   alert("撤销错误，请重试！！");
		 	   }
		});
	}
}

function getUrlArgStr(){ 
    var q=location.search.substr(1); 
    var qs=q.split('&'); 
    var argStr=''; 
    if(qs){ 
        for(var i=0;i<qs.length;i++){ 
            argStr+=qs[i].substring(0,qs[i].indexOf('='))+'='+qs[i].substring(qs[i].indexOf('=')+1)+'&'; 
        } 
    } 
    return argStr; 
}

function opena(id,v){
	if(window.confirm("确认打开？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:false,
			   data: "action=open&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   if(msg=="1"){
					  alert("打开成功");
					  location.href=base_url + "executive/?" + getUrlArgStr();
				   }else{
					   alert(msg);
					}
			   },
		 	   error: function(e){
		 		   alert("打开错误，请重试！！");
		 	   }
		});
	}
}

function closea(id,v){
	if(window.confirm("确认关闭？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:false,
			   data: "action=close&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   if(msg=="1"){
					  alert("关闭成功");
					  location.href=base_url + "executive/?" + getUrlArgStr();
				   }else{
					   alert(msg);
					}
			   },
		 	   error: function(e){
		 		   alert("关闭错误，请重试！！");
		 	   }
		});
	}
}
</script>
</body>
</html>
