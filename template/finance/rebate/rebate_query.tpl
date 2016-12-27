<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/default/easyui.css"/>
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/icon.css"/>
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 返点开票管理</div>
		<div class="tab">
      		<ul>
        		<li><a href="[BASE_URL]finance/rebate/?o=manager">返点开票管理</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=apply_manager">返点开票申请管理</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=invoice_nocollection">已开票未回款查询</a></li>
        		<li class="on"><a>返点查询</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=rebate_transfer_list">返点转移申请列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td>
      					客户名称&nbsp;<input type="text" name="cusname" id="cusname" style="height:20px;"/>&nbsp;&nbsp;
      					媒体名称&nbsp;<input type="text" name="medianame" id="medianame" style="height:20px;"/>&nbsp;&nbsp;
      					部门名称&nbsp;<input type="text" name="depname" id="depname" style="height:20px;"/>&nbsp;&nbsp;
      					开始时间&nbsp;<input type="text" name="startdate" id="startdate" class="Wdate" onclick="WdatePicker()"/>&nbsp;&nbsp;
      					结束时间&nbsp;<input type="text" name="enddate" id="enddate" class="Wdate" onclick="WdatePicker()"/>&nbsp;&nbsp;
      					<input type="button" value=" 搜索 " class="btn" id="sbtn"/>
      				</td>
      			</tr>
      		</table>
      		<p/>
      		<table id="dg" style="width:100%"></table>
      		<p></p>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script src="[BASE_URL]script/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {	
	$("#dg").datagrid({
		title:'返点详情列表',
		autoRowHeight:true,
		striped:true,
		columns:[[
			{field:'a',width:'100',align:'center',title:"时间段"},
			{field:'b',width:'100',title:"客户名称"},
			{field:'c',width:'100',title:"媒体名称"},
			{field:'d',width:'100',title:"部门名称"},
			{field:'e',width:'100',align:'right',title:"待分配返点金额"},
			{field:'f',width:'100',align:'right',title:"已开票返点金额"},
			{field:'g',width:'100',align:'right',title:"无需开票返点金额"}
		]]
	});

	

	$("#sbtn").click(function(){
		 dosearch();
	});
});

function dosearch(){
	var cusname = $.trim($("#cusname").val());
	var medianame = $.trim($("#medianame").val());
	var depname = $.trim($("#depname").val());
	var startdate = $.trim($("#startdate").val());
	var enddate = $.trim($("#enddate").val());

	if(cusname=="" && medianame=="" && depname=="" && startdate=="" && enddate==""){
		alert("请至少输入一个搜索条件");
	}else{
		var url = base_url + "get_data.php?action=getRebateQuery&medianame=" + encodeURI(medianame) + "&cusname=" + encodeURI(cusname) + "&depname=" + encodeURI(depname) + "&startdate=" +  encodeURI(startdate) + "&enddate=" +  encodeURI(enddate);
		$("#dg").datagrid({
			url:url
		});
	}
}

</script>
</body>
</html>
