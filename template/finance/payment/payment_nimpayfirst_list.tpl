<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/default/easyui.css"/>
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/icon.css"/>
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 垫付管理</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>垫付清单</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=nimpayfirst">垫付管理</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table id="dg" style="width:100%;"></table>
      		<p/>
      		<table id="dg1" style="width:100%;"></table>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>


<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/datagrid-groupview.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {	






	



	
	$("#sbtn").click(function(){
		 dosearch(1);
	});

	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

	$("#customer_search").click(function(){
		dosearch(1,'customer');
	});

	$("#payment_apply_search").click(function(){
		dosearch(1,'apply');
	});

	$("#dg").datagrid({
		title:'垫付部门列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		singleSelect:true,
		url:base_url + "get_data.php?action=getDepartNimpayfirst",
		columns:[[
			{field:'a',width:'200',title:"部门名称"},
			{field:'b',width:'200',title:"负责人"},
			{field:'c',width:'200',align:'right' ,title:"已垫付未回款金额"},
			{field:'d',width:'200',align:'center' ,title:"操作"}
		]]
	});

	$("#dg1").datagrid({
		title:'客户合同款垫付记录',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
         columns:[[
			{field:'aa',width:120,title:"执行单号"},
			{field:'bb',width:200,title:"客户名称"},
			{field:'cc',width:120,title:"项目负责人"},
			{field:'dd',width:120,align:'right',title:"客户执行收入"},
			{field:'ee',width:120,align:'right',title:"已收客户款合计金额"},
			{field:'ff',width:120,align:'right',title:"已开票合计金额"},
			{field:'gg',width:120,align:'right',title:"已执行未到客户款金额"},
			{field:'hh',width:120,align:'right',title:"媒体已付款总金额"},
			{field:'ii',width:120,align:'right',title:"该媒体本次申请付款金额"},
			{field:'jj',width:120,align:'right',title:"该媒体本次申请垫付金额"},
			{field:'kk',width:120,align:'right',title:"系统计算的垫款金额"},
			{field:'ll',width:120,align:'right',title:"未回款金额"},
			//{field:'mm',width:120,title:"操作"},
		]]
	});
});


function dosearch(page,type){
	var check = false;
	if(type == "customer"){
		if($.trim($("#custom_name").val()) != "" || $.trim($("#starttime").val()) != "" || $.trim($("#endtime").val()) != ""){
			check = true;
		}
	}else if(type == "apply"){
		if($.trim($("#media_name").val()) != "" || $.trim($("#paytime").val()) != "" || $.trim($("#payamount").val()) != ""){
			check = true;
		}
	}

	if(!check){
		alert("请至少输入一个搜索条件");
	}else{
		if(type == "customer"){
			var data = "custom_name=" + $.trim($("#custom_name").val()) + "&starttime=" + $.trim($("#starttime").val()) + "&endtime=" + $.trim($("#endtime").val());
		}else{
			var data = "paymentype=" + $('input[name="paymentype"]:checked').val() + "&media_name=" + $.trim($("#media_name").val())  + "&paytime=" + $.trim($("#paytime").val()) + "&payamount=" +  $.trim($("#payamount").val());
		}
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_payfirst&page=" + page + "&type=" + type + "&" + data + "&t=" + Math.random() + "&vcode=" + vcode,
			dataType:'text',
			async: false,
			success: function(msg){
				$("#result_show").html(msg);
			},
		 	error: function(e){
		 		alert("搜索垫付记录异常");
		 	}
		});

	}
}

function openit(cdt){
	cdt = cdt.split("_");
	$("#dg1").datagrid({
		url:base_url + "get_data.php?action=getPayFirstByDepartment&city=" + cdt[0] + "&dep=" +  cdt[1] + "&team=" + cdt[2],
	});
}
</script>
</body>
</html>
