<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
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
        		<li class="on"><a>已开票未回款查询</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=query">返点查询</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=rebate_transfer_list">返点转移申请列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table id="dg" style="width:100%"></table>
      		<p/>
      		<table id="infodg" style="width:100%"></table>
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
		title:'已开票未回款列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		url:base_url + "get_data.php?action=getSumRebateInvoiceNoCollection",
		columns:[[
			{field:'a',width:'200',title:"媒体名称"},
			{field:'b',width:'200',align:'right',title:"未回款金额"},
			{field:'c',width:'200',title:"操作"}
		]]
	});
});

function openit(invoice_id){
	$("#infodg").datagrid({
		title:'详情列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		url:base_url + "get_data.php?action=getRebateInvoiceNoCollectionByInvoiceID&invoice_id=" + invoice_id,
		columns:[[
			{field:'aa',width:'200',align:'right',title:"开票金额"},
			{field:'bb',width:'200',align:'center',title:"开票时间"},
			{field:'cc',width:'200',title:"申请人"},
			{field:'dd',width:'200',title:"发票号码"},
			{field:'ee',width:'200',align:'right',title:"未回款金额"}
		]]
	});
	
}

</script>
</body>
</html>
