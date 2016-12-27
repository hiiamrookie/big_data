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
        		<li class="on"><a>垫付管理</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td style="font-weight:bold; width:150px">目前垫款总金额</td>
      				<td><span style="color:#ff9933; font-size:14px">[SUMAMOUNT]</span>&nbsp;元</td>
      			</tr>
      			<tr>
      				<td style="font-weight:bold; width:150px">未回款总金额</td>
      				<td><span style="color:#ff9933; font-size:14px">[SUMUNBACKAMOUNT]</span>&nbsp;元&nbsp;&nbsp;<input type="button" class="longbtn" value="&nbsp;垫付清单&nbsp;" id="listid"/></td>
      			</tr>
      		</table>
      		<p/>
      		<table  width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td style="font-weight:bold; ">按客户搜索</td>
      			</tr>
      			<tr>
      				<td>
      				客户名称&nbsp;&nbsp;
      				<input type="text" style="height:20px;" id="custom_name" name="custom_name"/>&nbsp;
      				开始时间&nbsp;&nbsp;
      				<input type="text" id="starttime" name="starttime" onclick="WdatePicker();" class="text Wdate" readonly="readonly"/>&nbsp;
      				结束时间&nbsp;&nbsp;
      				<input type="text" id="endtime" name="endtime" onclick="WdatePicker();" class="text Wdate" readonly="readonly"/>&nbsp;<input type="button" class="btn" value="&nbsp;搜索&nbsp;" id="customer_search"/>
      				</td>
      			</tr>
      		</table>
      		<p/>
      		<table  width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td style="font-weight:bold; ">按付款申请搜索</td>
      			</tr>
      			<tr>
      				<td>
      				<input type="radio" name="paymentype" value="1" checked/>&nbsp;付合同款&nbsp;&nbsp;<input type="radio" name="paymentype" value="2"/>&nbsp;付保证金&nbsp;&nbsp;
      				媒体名称&nbsp;&nbsp;
      				<input type="text" style="height:20px;" id="media_name" name="media_name"/>&nbsp;
      				付款时间&nbsp;&nbsp;
      				<input type="text" id="paytime" name="paytime" onclick="WdatePicker();" class="text Wdate" readonly="readonly"/>&nbsp;
      				付款金额&nbsp;&nbsp;
      				<input type="text" id="payamount" name="payamount" style="height:20px;"/>&nbsp;<input type="button" class="btn" value="&nbsp;搜索&nbsp;" id="payment_apply_search"/>
      				</td>
      			</tr>
      		</table>
      		<p></p>
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td style="font-weight:bold; ">搜索结果</td>
      			</tr>
      			<tr>
      				<td id="result_show"></td>
      			</tr>
      		</table>
      		<p/>
      		<table id="dg" style="width:100%;"></table>
      		<div id="toolbar1" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn">删除</a>&nbsp;&nbsp;
						收件人邮件地址：<input type="text" class="validate[required,custom[email]]" style="height:20px;width:200px;" name="to_email" id="to_email"/>&nbsp;&nbsp;
						抄送人邮件地址：<input type="text" class="validate[optional,custom[email]]"  style="height:20px;width:200px;" name="cc_email" id="cc_email"/>&nbsp;&nbsp;
						<input type="hidden" name="pids" id="pids"/><input type="button" value="发送邮件" class="longbtn" id="sendbtn"/><input type="hidden" name="action" id="action" value="sendPayfirstRemindEmail"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/>
					</div>
				</div>
      		<p/>
      		<table id="dg1" style="width:100%;"></table>
      		<p/>
      		<table id="dg2" style="width:100%;"></table>
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
	$("#listid").click(function(){
		location.href=base_url + "finance/payment/?o=nimpayfirst_list";
	});
	
	$("#sendbtn").click(function(){
		var rows = $('#dg').datagrid('getSelections');
		if(rows.length == 0){
			alert("请选择至少一条记录");
		}else{
			var pids = new Array();
			for(var i=0;i<rows.length;i++){
				pids.push(rows[i].a);
			}
			$("#pids").val(pids.join(","));
			$("#formID").submit();
		}
	});






	
	//提交按钮
	$("#submitb").click(function(){
		//应付金额
		$("#payment_amount_plan").removeClass("validate[optional,custom[money]]");
		$("#payment_amount_plan").addClass("validate[required,custom[money]]");

		//付款时间
		$("#payment_date").addClass("validate[required]");

		//action
		$("#action").val("payment_person_apply");
		$("#formID").submit();
	});

	//保存按钮
	$("#save").click(function(){
		//应付金额
		$("#payment_amount_plan").removeClass("validate[required,custom[money]]");
		$("#payment_amount_plan").addClass("validate[optional,custom[money]]");

		//付款时间
		$("#payment_date").removeClass("validate[required]");

		//action
		$("#action").val("payment_person_apply_temp");
		
		$("#formID").submit();
	});


	
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
		title:'按付款申请搜索垫付信息结果列表',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		toolbar:'#toolbar1',
		columns:[[
			{field:'x',checkbox:true},
			{field:'a',width:'120',title:"执行单号"},
			{field:'b',width:'180',title:"客户名称"},
			{field:'c',width:'140',title:"部门"},
			{field:'d',width:'140',title:"项目负责人"},
			{field:'e',width:'140',title:"项目名称"},
			{field:'f',align:'right',width:'120',title:"客户执行收入"},
			{field:'g',align:'right',width:'120',title:"已收客户款合计金额"},
			{field:'h',align:'right',width:'120',title:"已开票合计金额"},
			{field:'i',align:'right',width:'120',title:"执行未到客户款金额"},
			{field:'j',align:'right',width:'120',title:"媒体已付款总金额"},
			{field:'k',align:'right',width:'120',title:"该媒体本次申请付款金额"},
			{field:'l',align:'right',width:'120',title:"该媒体本次申请垫付金额"},
			{field:'m',align:'right',width:'120',title:"系统计算的垫款金额"},
			{field:'n',align:'right',width:'120',title:"未回款金额"},
			{field:'o',width:'150'}
		]]
	});

	$("#dg1").datagrid({
		title:'客户合同款垫付记录',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
         columns:[[
			{field:'aa',width:120,title:"执行单号"},
			{field:'bb',width:200,title:"项目名称"},
			{field:'cc',width:120,align:'right',title:"客户执行收入"},
			{field:'dd',width:120,align:'right',title:"已收客户款合计金额"},
			{field:'ee',width:120,align:'right',title:"已开票合计金额"},
			{field:'ff',width:120,align:'right',title:"已执行未到客户款金额"},
			{field:'gg',width:120,align:'right',title:"媒体已付款总金额"},
			{field:'hh',width:120,title:"申请垫付对象"},
			{field:'ii',width:120,align:'center',title:"申请垫付时间"},
			{field:'jj',width:120,align:'right',title:"申请垫付金额"},
			{field:'kk',width:120,align:'right',title:"系统计算的垫款金额"},
			{field:'ll',width:120,align:'right',title:"未回款金额"},
			//{field:'mm',width:120,title:"操作"},
		]]
	});

	$("#dg2").datagrid({
		title:'客户保证金垫付记录',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
         columns:[[
			{field:'aaa',width:120,title:"合同号"},
			{field:'bbb',width:120,align:'right',title:"保证金合计金额"},
			{field:'ccc',width:120,align:'right',title:"已收客户保证金合计金额"},
			{field:'ddd',width:120,align:'right',title:"媒体已付保证金合计"},
			{field:'eee',width:120,align:'right',title:"该媒体本次保证金申请付款金额"},
			{field:'fff',width:120,align:'right',title:"该媒体本次保证金申请垫付金额"},
			{field:'ggg',width:120,align:'right',title:"系统计算的垫款金额"},
			{field:'hhh',width:120,align:'right',title:"未回款金额"},
			//{field:'iii',width:120,title:"操作"},
		]]
	});

	//openit(3,'pc');
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

function openit(apply_id,stype){
	var pids = "";
	var url = base_url + "get_data.php?action=getPayFirstByPaymentApply&apply_id=" + apply_id + "&stype=" + stype;
	
	$("#dg").datagrid({
		url:url,
	});

}

function openitit(cusname){
	$("#dg1").datagrid({
		url:base_url + "get_data.php?action=getCustomerContractPaymentNimpayfirst&cusname=" + encodeURI(cusname),
	});

	$("#dg1").datagrid({
		url:base_url + "get_data.php?action=getCustomerDepositPaymentNimpayfirst&cusname=" + encodeURI(cusname),
	});
}
</script>
</body>
</html>
