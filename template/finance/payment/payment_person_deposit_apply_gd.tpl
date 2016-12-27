<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理</title>
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
		<div class="crumbs">财务管理 - 付款申请</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>归档个人申请付保证金</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=person_deposit_apply_manager">个人申请付保证金列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      			[PAYMENTPERSONINFO]
			<p></p>
			<div>
			<p></p>
			<table  id="paymentdg"></table>
				<p></p>
				<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
					<tr><td style="font-weight:bold; width:150px">付款时间</td><td><input type="text" name="paymentdate" id="paymentdate" onclick="WdatePicker()" class="text Wdate" readonly="readonly"/></td></tr>
					<tr><td style="font-weight:bold; width:150px">付款金额</td><td><input type="text" name="paymentamount" class="text"/></td></tr>
					<tr><td style="font-weight:bold; width:150px">付款方式</td><td><input type="radio" name="paymenttype" value="1" checked/>&nbsp;现金支付&nbsp;&nbsp;<input type="radio" name="paymenttype" value="2"/>&nbsp;银行转账&nbsp;&nbsp;<select name="paymentbank">[NIMBANKS]</select></td></tr>
				</table>
			</div>
      		<div class="btn_div">
        		<input type="hidden" name="id" id="id" value="[APPLYID]"/><input type="hidden" name="gdvalues" id="gdvalues"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="payment_deposit_person_gd"/><input type="button" value="提交" class="btn_sub" id="submitb" name="subbtn"/>
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
<script type="text/javascript" src="[BASE_URL]script/jquery.autocomplete.min.js" language="javascript"></script>
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
	$("#paymentdg").datagrid({
		url:base_url + "get_data.php?action=getPersonDepositPaymentGD&apply_id=" + encodeURI($("#id").val()),
		height:"auto",
		title:"已付款记录",
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		columns:[[
		 {field:'paymentdate',width:100,title:"付款时间"},
         {field:'paymentamount',width:200,title:"实付金额"},
          {field:'paymentype',width:200,title:"付款方式"},
         {field:'paymentbank',width:200,title:"付款行"}
          ]]
	});
	
	$('#depositdg').datagrid({
	    toolbar: '#tb',
	    pagination : true,
	    striped : true,
	    pageList : [10,20,30],
	    fit : true,
	    url : base_url + 'get_data.php',
	    columns:[[
	        {field:'ck',title:'',checkbox:true},
	        {field:'cid',title:'合同号'},
	        {field:'cusname',title:'客户名称'},
	        {field:'medianames',title:'媒体名称'},
	        {field:'sumdepositpayment',title:'已付媒体保证金',align:'right'},
	        {field:'opt',title:'操作',align:'center'}
	    ]]
	});
	
	$("#depositbtn").click(function(){
		if($("#is_deposit_deduction").prop("checked") == true){
			$('#dd').dialog({
			    closed: false
			});
		}
	});
	
	//提交按钮
	$("#submitb").click(function(){
		var rows = $('#dg').datagrid('getSelections');
		var gdvalues = new Array();
		if(rows.length >0){
			for(var i=0;i<rows.length;i++){
				gdvalues.push(rows[i].y);
			}
			$("#gdvalues").val(gdvalues.join(","));
		}else{
			$("#gdvalues").val("");
			alert("请选择需要归档的记录");
		}
		$("#formID").submit();
	});


	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"topRight", 
	    scroll:false
	});


	//加载已有数据
	$.ajax({
		   type: "post",
		   url: base_url + "get.php",
		   cache:"false",
		   data: "action=get_payment_deposit_apply_cidinfo&id=[APPLYID]&t=" + Math.random() + "&vcode=" + vcode,
		   dataType:'text',
		   async: false,
		   success: function(msg){
			   rows = $.parseJSON(msg);
			   for (var i=0;i<rows.rows.length;i++){
				   $('#dg').datagrid('appendRow',rows.rows[i]);
				}
		   },
	 	   error: function(e){
	 		   alert("查找相关数据异常");
	 	   }
	});
});

function auditem(payment_list_id,itemid){
	var sid = "auditsel_" + itemid;
	var tid = "auditresaon_" + itemid;
	$.ajax({
		   type: "post",
		   url: base_url + "finance/payment/do.php",
		   cache:"false",
		   data: "action=auditem&apply_id=" + $("#id").val() + "&listid=" + payment_list_id + "&auditsel=" + $('input[name="' + sid + '"]:checked').val() + "&auditresaon=" + $("#" + tid).val() + "&t=" + Math.random() + "&vcode=" + vcode,
		   dataType:'text',
		   async: false,
		   success: function(msg){
			  
		   },
	 	   error: function(e){
	 		   alert("提交审核数据异常");
	 	   }
	});
	
}
</script>
</body>
</html>
