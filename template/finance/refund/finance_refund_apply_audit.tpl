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
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
<style>
html, body {
	margin: 0;			/* Remove body margin/padding */
	padding: 0;
}
</style>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 退款申请</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>审核个人申请退客户款</a></li>
        		<li><a href="[BASE_URL]finance/refund/?o=manager">个人申请退客户款列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
          			<td style="font-weight:bold; width:150px">退款单号</td>
          			<td colspan="3">[REFUNDID]</td>
        		</tr>
       			<tr>
          			<td style="font-weight:bold; width:150px">客户名称</td>
          			<td colspan="3">[CUSTOMERNAME]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold; width:150px">开户行</td>
          			<td colspan="3">[BANKNAME]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold; width:150px">银行账号</td>
          			<td colspan="3">[BANKACCOUNT]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;">退款金额</td>
          			<td colspan="3">[REFUNDAMOUNT] 元</td>
       	 		</tr>
      			<tr>
          			<td style="font-weight:bold;width:150px">退款时间</td>
          			<td colspan="3">[REFUNDDATE]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">退款类型</td>
          			<td colspan="3">[REFUNDTYPE]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">退款理由</td>
          			<td colspan="3">[REFUNDREASON]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">终止协议上传</td>
          			<td colspan="3">[REFUNDDIDS]</td>
        		</tr>
      		</table>
      		<p/>
      		<table id="result_show" width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      		[REFUNDITEMS]
      		</table>
      		<p/>
      		<table>
      		<tr><td colspan="2"><input type="radio" name="auditall" value="1" checked/>&nbsp;审核通过&nbsp;&nbsp;<input type="radio" name="auditall" value="2"/>&nbsp;审核驳回</td></tr>
      		<tr><td>审核意见</td><td><textarea class="validate[optional,maxSize[500]]" name="auditremarkall" cols="50" rows="5"></textarea></td></tr>
      		</table>
      		<div class="btn_div">
        		<input type="hidden" name="id" id="id" value="[ID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="refund_apply_allaudit"/><input type="submit" value="提 交" class="btn_sub" id="submitb" name="subbtn"/>
      		</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
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
	$("#addbtn").click(function(){
		var selpcids = new Array();
		$('input[name="selpcid"]').each(function(){
			if($(this).attr("checked") == "checked"){
				selpcids.push($(this).val());
			}
		});
		if(selpcids.length == 0){
			alert("请选择记录");
		}else{
			var pids = $("#pids").val();
			for(var i=0;i<selpcids.length;i++){
				if(pids.indexOf("," + selpcids[i] + ",") == -1){
					pids += selpcids[i] + ",";
					var hashtml = $("#result_show").html();
					if(hashtml == ""){
						$("#result_show").append("<tr><td></td><td>执行单号</td><td>客户名称</td><td>已收金额</td><td>已开票金额</td><td>退款金额</td></tr>");
					}
					var pid = "#pid_" + selpcids[i];
					var cusname = "#cusname_" + selpcids[i];
					var amount = "#amount_" + selpcids[i];
					var invoice_amount = "#invoice_amount_" + selpcids[i];
					$("#result_show").append("<tr id=\"addtr_" + selpcids[i] + "\"><td><img src=\"" + base_url + "images/close.png\" onclick=\"pidmove('" + selpcids[i] + "');\"/></td><td>" + $(pid).html() + "</td><td>" + $(cusname).html() + "</td><td>" + $(amount).html() + "</td><td>" + $(invoice_amount).html() + "</td><td><input type=\"text\" class=\"validate[required,max[0],min[-" + $(amount).html() + "]]\" style=\"height:20px;\" name=\"refund_" + selpcids[i] + "\" value=\"-" + $(amount).html() + "\"></td><</tr>");
					
				}
			}
			$("#pids").val(pids);
		}
	});
	
	$("#sbtn").click(function(){
		 dosearch(1);
	});

	$("#customer_name_select").change(function(){
		$("#bank_name_select").empty();
		$("#bank_name_select").append("<option value=\"\">请选择开户行</option>");
		$("#bank_account_select").empty();
		$("#bank_account_select").append("<option value=\"\">请选择银行账号</option>");
		if($(this).val() != ""){	
			$.ajax({
				type: "POST",
				url: "do.php",
				cache:"false",
				data: "action=search_customer_bank&customer_name=" + $(this).val() + "&t=" + Math.random() + "&vcode=" + vcode,
				dataType:'text',
				async: false,
				success: function(msg){
					if(msg!=""){
						$("#bank_name_select").append(msg);
					}
				},
				 error: function(e){
				 	alert("获取客户开户行出错");
				 }
			});
		}
	});

	$("#bank_name_select").change(function(){
		$("#bank_account_select").empty();
		$("#bank_account_select").append("<option value=\"\">请选择银行账号</option>");
		if($(this).val() != ""){
			$.ajax({
				type: "POST",
				url: "do.php",
				cache:"false",
				data: "action=search_customer_bank_account&customer_name=" + $("#customer_name_select").val() + "&bank_name=" + $(this).val() + "&t=" + Math.random() + "&vcode=" + vcode,
				dataType:'text',
				async: false,
				success: function(msg){
					if(msg!=""){
						$("#bank_account_select").append(msg);
					}
				},
				 error: function(e){
				 	alert("获取客户银行账号出错");
				 }
			});
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

function dosearch(page){
	if($.trim($("#search_pcid").val())=="" && $.trim($("#cusname").val())==""){
		alert("请至少输入一个搜索条件");
	}else{
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_receivable&page=" + page + "&refundment_type=" + $('input[name="refundment_type"]:checked').val()+ "&search=" + $("#search_pcid").val() + "&cusname=" + $("#cusname").val() + "&t=" + Math.random() + "&vcode=" + vcode,
			dataType:'text',
			async: false,
			success: function(msg){
				$("#search_result").empty();
				$("#search_result").append(msg);
			},
		 	error: function(e){
		 		alert("搜索记录异常");
		 	}
		});
	}
}

function pidmove(pid){
	var addtr = "#addtr_" + pid;
	$(addtr).remove();
	var nowpids = $("#pids").val();
	$("#pids").val(nowpids.replace("," + pid + ",",","));
	
	var sel = false;
	$(":checkbox").each(function(index, element) {
		if ($(this).attr("checked")=="checked" && $(this).val() !="1"){
			var nowpids = $("#pids").val();
			if(nowpids.indexOf("," + $(this).val() + ",") == -1){
				if(!sel){
					sel = true;
				}
				$("#pids").val(nowpids + $(this).val() + ",");
			}
		}
	});
}

function removepid(v,pid){
	$(v).parent().parent().remove();
	var nowpids = $("#pids").val();
	$("#pids").val(nowpids.replace("," + pid + ",",","));
}

function selectall(obj){
	var checked = false;
	if(obj.checked){
		checked = true;
	}
	$("input[name='selexe']").each(function(){
		$(this).attr("checked",checked);
	});
}

function check_select_all(obj){
	if(!obj.checked){
		$("#selall").attr("checked",false);
	}else{
		var sall = true;
		$("input[name='selexe']").each(function(){
			if($(this).attr("checked")!="checked" && sall){
				sall = false;
				return false;
			}
		});
		if(sall){
			$("#selall").attr("checked",sall);
		}
	}
}

function openit(pid){
	var id = "trr_" + pid;
	$("#" + id).toggle();
}

function set_refundment_type(obj){
	if(obj.value == 1){
		//搜索执行单
		$("#searchshow").html("执行单号");
	}else{
		//搜索合同号
		$("#searchshow").html("合同号");
	}
}

function sub(pid,applyid){
	if(window.confirm("确认审核该退款申请？")){
		var itemaudit = "itemaudit_" + pid;
		var remark = "remark_" + pid;
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=audit_refund&pid=" + pid + "&applyid=" + applyid +  "&itemaudit=" + $('input[name="' + itemaudit + '"]:checked').val() + "&remark=" + $("#" + remark).val() + "&t=" + Math.random() + "&vcode=" + vcode,
			dataType:'text',
			async: false,
			success: function(msg){
				if(msg == "1"){
					location.href=base_url + "finance/refund/?o=manager";
				}else if(msg == "2"){
					location.href=base_url + "finance/refund/?o=audit&id=" + applyid;
				}else{
					alert(msg);	
				}
			},
		 	error: function(e){
		 		alert("审核退款申请异常");
		 	}
		});
	}
}
</script>
</body>
</html>
