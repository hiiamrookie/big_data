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
        		<li class="on"><a>归档返点开票申请</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=apply_manager">已申请返点开票列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		[REBATEINVOICEINFO]
      		<p/>
      		<table id="mdg" width="100%"></table>
      		<p/>
      		<table id="idg" width="100%"></table>
      		<p/>
      		 <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1"> 
      		 	<tr>
      		 		<td colspan="3" style="font-weight:bold;">进款归档</td>
      		 	</tr>
      			<tr>
      				<td  style="font-weight:bold;width:33%">进款时间</td><td  style="font-weight:bold;width:33%">进款金额</td><td  style="font-weight:bold;">银行</td>
      			</tr>
      			<tr>
      				<td><input type="text" style="height:20px;" name="m_date" id="m_date" class="validate[required] Wdate" onclick="WdatePicker()"/></td><td><input type="text" style="height:20px;" name="m_amount" id="m_amount" class="validate[required,custom[money]]"/></td><td><select name="m_bank" id="m_bank" class="validate[required]">[BANKLIST]</select></td>
      			</tr>
      		</table>
      		<div class="btn_div">
      			<input type="button" value="进款归档提交" class="btn_sub" id="moneybtn" name="moneybtn"/>
      		</div>
      		<p/>
      		 <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1"> 
      		 	<tr>
      		 		<td colspan="3" style="font-weight:bold;">开票归档</td>
      		 	</tr>
      			<tr>
      				<td  style="font-weight:bold;width:33%">开票时间</td><td  style="font-weight:bold;width:33%">开票金额</td><td  style="font-weight:bold;">开票号码</td>
      			</tr>
      			<tr>
      				<td><input type="text" style="height:20px;" name="i_date" id="i_date" class="validate[required] Wdate" onclick="WdatePicker()"/></td><td><input type="text" style="height:20px;" name="i_amount" id="i_amount" class="validate[required,custom[money]]"/></td><td><input type="text" style="height:20px;" name="i_number" id="i_number" class="validate[required,maxSize[100]]"/></td>
      			</tr>
      		</table>
      		<div class="btn_div">
      			<input type="hidden" name="id" id="id" value="[ID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="rebate_invoice_gd"/><input type="button" value="开票归档提交" class="btn_sub" id="invoicebtn" />
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
	$("#mdg").datagrid({
		title:'已归档进款列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		singleSelect:false,
		url:base_url + "get_data.php?action=getRebateInvoiceGDInfo&id=" + $("#id").val() + "&type=2",
		columns:[[
                {field:'a2',width:'120',align:'center',title:"进款时间"},
                {field:'b2',width:'180',align:'right',title:"进款金额"},
                {field:'c2',width:'300',title:"银行及账户"}
		]]
	});

	$("#idg").datagrid({
		title:'已归档开票列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		singleSelect:false,
		url:base_url + "get_data.php?action=getRebateInvoiceGDInfo&id=" + $("#id").val() + "&type=1",
		columns:[[
                {field:'a1',width:'120',align:'center',title:"开票时间"},
                {field:'b1',width:'180',align:'right',title:"开票金额"},
                {field:'c1',width:'300',title:"开票号码"}
		]]
	});


	
	$("#moneybtn").click(function(){
		$("#i_date").removeClass("validate[required]");
		$("#i_amount").removeClass("validate[required,custom[money]]");
		$("#i_number").removeClass("validate[required,maxSize[100]]");

		$("#m_date").addClass("validate[required]");
		$("#m_amount").addClass("validate[required,custom[money]]");
		$("#m_bank").addClass("validate[required]");
		
		$("#action").val("rebate_invoice_collection_gd");
		$("#formID").submit();
	});

	$("#invoicebtn").click(function(){
		$("#m_date").removeClass("validate[required]");
		$("#m_amount").removeClass("validate[required,custom[money]]");
		$("#m_bank").removeClass("validate[required]");
		
		$("#i_date").addClass("validate[required]");
		$("#i_amount").addClass("validate[required,custom[money]]");
		$("#i_number").addClass("validate[required,maxSize[100]]");

		$("#action").val("rebate_invoice_gd");
		$("#formID").submit();
	});
	
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
