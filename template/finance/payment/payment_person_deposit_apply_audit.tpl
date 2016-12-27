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
        		<li class="on"><a>审核个人申请保证金付款</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=person_deposit_apply_manager">个人申请付保证金款列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      			[PAYMENTPERSONINFO]
			<p></p>
			<div>
			<table>
			<tr><td style="font-weight:bold;width:100px">审核意见</td><td><textarea id="remark" class="validate[optional,maxSize[1000]] textarea" name="remark" rows="3" style="width:400px;height:80px"></textarea></td></tr>
			</table>
			</div>
      		<div class="btn_div">
        		<input type="hidden" name="deposit_deductiuon" id="deposit_deductiuon" value="[DEPOSITDEDUCTION]"/><input type="hidden" name="id" id="id" value="[APPLYID]"/><input type="hidden" name="auditvalue" id="auditvalue" value="pass"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="audit_full_payment_person_deposit"/><input type="button" value="审核通过" class="btn_sub" id="submitb" name="subbtn"/>&nbsp;<input type="button" value="审核驳回" class="btn_sub" id="rejectb" name="rejbtn"/>
      		</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<!--div id="dd">
	<table id="depositdg"></table>
		<div id="tb" style="padding:5px;height:auto">
			媒体名称&nbsp;<input type="text" id="searchmedianame" class="easyui-textbox" style="width:150px;" data-options="prompt:'媒体名称关键字'"/>&nbsp;&nbsp;
			客户名称&nbsp;<input type="text" id="searchcusname" class="easyui-textbox" style="width:150px;" data-options="prompt:'客户名称关键字'"/>&nbsp;&nbsp;
			合同号&nbsp;<input type="text" id="searchcid" class="easyui-textbox" style="width:150px;" data-options="prompt:'合同号关键字'"/>&nbsp;&nbsp;
			<a href="javascript:void(0);" class="easyui-linkbutton" data-options="iconCls:'icon-search'" id="searchbtn">搜索</a>&nbsp;&nbsp;
			<a href="javascript:void(0);" class="easyui-linkbutton" data-options="iconCls:'icon-add'" id="addbtn">添加</a>
		</div>
</div-->
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
	//审核通过按钮
	$("#submitb").click(function(){
		$("#auditvalue").val("pass");
		$("#formID").submit();
	});

	//审核驳回按钮
	$("#rejectb").click(function(){
		if(window.confirm("确认审核驳回该付款申请？")){
			$("#auditvalue").val("reject");
			$("#formID").submit();
		}
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

	$("#selectdeposit").datagrid({
		title: "所选保证金数据",
	    striped : true,
	    url : base_url + 'get_data.php?action=getDepositDeduction&apply_id=' + $("#id").val() + "&deposit_deductiuon=" + $("#deposit_deductiuon").val() + "&payment_type=2&isshow=1",
	    columns:[[
	        {field:'dcid',title:'合同号',width:200},
	        {field:'dcusname',title:'客户名称',width:200},
	        {field:'dmedia',title:'媒体名称',width:200},
	        {field:'dgd_amount',title:'已付媒体保证金',align:'right',width:200},
	        {field:'ddeduction',title:'使用保证金金额',align:'right',width:200}
	    ]]
	});
});

function auditem(payment_list_id,itemid){
	var sid = "auditsel_" + itemid;
	var tid = "auditresaon_" + itemid;
	var auditsel = $('input[name="' + sid + '"]:checked').val();
	if(window.confirm("确定" + (auditsel==1 ? "通过" : "驳回") + "该条目？")){
		$.ajax({
			   type: "post",
			   url: base_url + "finance/payment/do.php",
			   cache:"false",
			   data: "action=auditemDeposit&apply_id=" + $("#id").val() + "&listid=" + payment_list_id + "&auditsel=" + $('input[name="' + sid + '"]:checked').val() + "&auditresaon=" + $("#" + tid).val() + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   if(msg == "1"){
						var id = "auditres_" + payment_list_id;
						$("#" + id).html((auditsel =="1" ? "通过" : "驳回"));
					}else{
						alert(msg);
					}
			   },
		 	   error: function(e){
		 		   alert("提交审核数据异常");
		 	   }
		});
	}
}
</script>
</body>
</html>
