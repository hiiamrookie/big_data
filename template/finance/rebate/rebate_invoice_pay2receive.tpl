<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
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
		<div class="crumbs">财务管理 - 返点开票申请</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>应付转应收</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=rebate_transfer_list">返点转移申请列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    	<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      			[REBATEINVOICEINFO]
      	<p/>
      	 <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1"> 
      	 	<tr>
      	 		<td colspan="9">关联付款申请</td>
      	 	</tr>
      	 	<tr>
      	 		<td style="font-weight:bold;width:150px">媒体名称</td><td><input type="text" name="searchmedianame" id="searchmedianame" style="height:20px;"/></td>
      	 		<td style="font-weight:bold;width:150px">付款时间</td><td><input type="text" name="searchpaydate" id="searchpaydate" style="height:20px;" onclick="WdatePicker()" class="Wdate"/></td>
      	 		<td style="font-weight:bold;width:150px">应付款金额</td><td><input type="text" name="searchpayplan" id="searchpayplan" style="height:20px;"/></td>
      	 		<td style="font-weight:bold;width:150px">实付款金额</td><td><input type="text" name="searchpayreal" id="searchpayreal" style="height:20px;"/></td>
      	 		<td><input type="button" value="搜索" name="sbtn" id="sbtn" class="btn"/></td>
      	 	</tr>
      	 </table>
      	 <p/>
      	 <table id="searchdg" width="100%"></table>
      	 <p/>
      	  <table id="paydg" width="100%"></table>
      	  <p/>
      	  <div class="btn_div">
        		<input type="hidden" name="id" id="id" value="[ID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="rebate_invoice_pay2receive"/><input type="hidden" name="pids" id="pids"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
      		</div>
      	  </form>
      	  <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {
	$("#pdg").datagrid({
		title:'返点开票列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		singleSelect:false,
		columns:[[
                {field:'a',width:'120',title:"执行单号"},
                {field:'b',width:'180',title:"客户名称"},
                {field:'c',width:'140',title:"项目名称"},
                {field:'d',width:'140',title:"供应商"},
                {field:'e',align:'right',width:'120',title:"媒体执行成本"},
                {field:'f',align:'right',width:'120',title:"媒体已付款金额"},
                {field:'g',align:'right',width:'120',title:"媒体已到票金额"},
                {field:'h',align:'right',width:'120',title:"已开返点发票金额"},
                {field:'i',width:'200',title:"本次开返点发票金额"}
		]]
	});

	$("#searchdg").datagrid({
		title:'付款申请搜索结果列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		singleSelect:true,
		pagination:true,
		columns:[[
		        //{field:'xx',checkbox:true},
                {field:'aa',width:'200',title:"媒体名称"},
                {field:'bb',width:'120',align:'center',title:"付款时间"},
                {field:'cc',width:'140',align:'right',title:"应付款金额"},
                {field:'dd',width:'140',align:'right',title:"实付款金额"},
                {field:'ee',width:'140',align:'right',title:"返点抵扣金额"},
                {field:'ff',width:'120',align:'center',title:"操作"}
		]]
	});

	$("#paydg").datagrid({
		title:'所选列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		singleSelect:false,
		columns:[[
                {field:'aaa',width:'120',title:"执行单号"},
                {field:'bbb',width:'200',title:"客户名称"},
                {field:'ccc',width:'200',title:"项目名称"},
                {field:'ddd',width:'140',align:'right',title:"收到真实发票"},
                {field:'eee',width:'140',align:'right',title:"虚拟发票"},
                {field:'fff',width:'140',align:'right',title:"已付款未到票"},
                {field:'ggg',width:'140',align:'right',title:"执行成本"},
                {field:'hhh',width:'140',align:'right',title:"已支付"},
                {field:'iii',width:'140',align:'right',title:"已执行未付款"},
                {field:'jjj',width:'140',align:'right',title:"返点抵扣"},
                {field:'kkk',width:'140',align:'right',title:"返点已开票"},
                {field:'lll',width:'140',align:'right',title:"返点未开票"},
                {field:'mmm',width:'140',align:'right',title:"返点无需开票"},
                {field:'nnn',width:'200',title:""}
		]]
	});

	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true)	$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});

	//加载已有数据
	$.ajax({
	   type: "post",
	   url: base_url + "get.php",
	   cache:"false",
	   data: "action=get_reabte_invoice_pidinfo&pids=" + pids + "&t=" + Math.random() + "&vcode=" + vcode,
	   dataType:'text',
	   async: false,
	   success: function(msg){
		  	rows = $.parseJSON(msg);
		  	if(rows.total != "0"){
			  	for (var i=0;i<rows.rows.length;i++){
			  		$('#pdg').datagrid('appendRow',rows.rows[i]);
				}
			}
	   },
 	   error: function(e){
 		   alert("查找相关数据异常");
 	   }
	});

	$("#sbtn").click(function(){
		 dosearch();
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

function dosearch(){
	var searchpid = $.trim($("#searchpid").val());
	var searchcusname = $.trim($("#searchcusname").val());
	var searchmedianame = $.trim($("#searchmedianame").val());
	var searchpaydate = $.trim($("#searchpaydate").val());
	var searchpayplan = $.trim($("#searchpayplan").val());
	var searchpayreal = $.trim($("#searchpayreal").val());
	
	if(searchpid=="" && searchcusname=="" && searchmedianame=="" && searchpaydate=="" && searchpayplan=="" && searchpayreal ==""){
		alert("请至少输入一个搜索条件");
	}else{
		var url = base_url + "get_data.php?action=getPaymentApplyInRebateTransfer&searchpid=" + encodeURI(searchpid) + "&searchcusname=" + encodeURI(searchcusname) + "&searchmedianame=" + encodeURI(searchmedianame) + "&searchpaydate=" +  encodeURI(searchpaydate) + "&searchpayplan=" +  encodeURI(searchpayplan) + "&searchpayreal=" +  encodeURI(searchpayreal);
		$("#searchdg").datagrid({
			url:url
		});
	}
}

function add(id){
	$("#paydg").datagrid({
		url:base_url + "get_data.php?action=getPaymentListInRebateTransfer&itemid=" + id,
		onLoadSuccess:function(data){
			$("#pids").val(data.pids);
		}
	});

	
}
</script>
</body>
</html>
