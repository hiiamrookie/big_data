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
      			<li><a href="[BASE_URL]finance/refund/?o=manager">个人申请退客户款列表</a></li>
        		<li class="on"><a>新增媒体退款记录</a></li>
        		<li><a href="[BASE_URL]finance/refund/?o=mediarefundlist">媒体退款列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
       			<tr>
          			<td style="font-weight:bold; width:150px">媒体名称</td>
          			<td colspan="3">
            			<input type="text" class="validate[required,maxSize[255]] " style="height:20px;width:300px;" id="media_name" name="media_name" />
          			</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">收款方式</td>
          			<td colspan="3"><input type="radio" name="receivables_type"  value="1" checked onclick="javascript:set_receivables_type(this);"/>&nbsp;现金&nbsp;&nbsp;<input type="radio" name="receivables_type"  value="2" onclick="javascript:set_receivables_type(this);"/>&nbsp;转账&nbsp;&nbsp;<select name="nimbank_id" id="nimbank_id" class="select" style="width:200px;"><option value="">请选择开户行</option>[NIMBANKSELECT]</select></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;">退款金额</td>
          			<td colspan="3">
          				<input type="text" class="validate[required,custom[money]]" style="height:20px;" id="refund_amount" name="refund_amount"/> 元
          			</td>
       	 		</tr>
      			<tr>
          			<td style="font-weight:bold;width:150px">退款时间</td>
          			<td colspan="3"><input type="text" id="refund_date" name="refund_date" onclick="WdatePicker({minDate:'%y-%M-%d'});" class="validate[required] text Wdate" readonly="readonly"/></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">退款类型</td>
          			<td colspan="3"><input type="radio" name="refund_type"  value="1" checked onclick="javascript:set_refundment_type(this);"/>&nbsp;合同款&nbsp;&nbsp;<input type="radio" name="refund_type"  value="2" onclick="javascript:set_refundment_type(this);"/>&nbsp;保证金</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">备注</td>
          			<td colspan="3"><textarea id="remark" class="validate[optional,maxSize[500]] textarea" name="remark" rows="3" style="width:400px;height:80px"></textarea></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold">搜索相关执行单</td>
          			<td colspan="3">
          				<span id="searchshow">执行单号</span>&nbsp;&nbsp;<input type="text"  style="height:20px;" id="search_pcid" name="search_pcid"/>&nbsp;&nbsp;&nbsp;
          				客户名称&nbsp;&nbsp;<input type="text"  style="height:20px;" id="cusname" name="cusname"/>&nbsp;&nbsp;&nbsp;
          				<input type="button" class="btn" value="搜 索" id="sbtn"/>&nbsp;&nbsp;<input type="button" class="btn" value="添 加" id="addbtn"/></td>
        		</tr>
        		<tr><td id="search_result" colspan="4"></td></tr>
      		</table>
      		<p/>
      		<table id="result_show" width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1"></table>
      		<p/>
      		<div class="btn_div">
        		<input type="hidden" name="pids" id="pids" value=","/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="meida_refund_add"/><input type="submit" value="提 交" class="btn_sub" id="submitb" name="subbtn"/>
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
			var refundment_type = $('input[name="refundment_type"]:checked').val();
			for(var i=0;i<selpcids.length;i++){
				if(pids.indexOf("," + selpcids[i] + ",") == -1){
					pids += selpcids[i] + ",";
					var hashtml = $("#result_show").html();
					if(hashtml == ""){
						$("#result_show").append("<tr><td></td><td>" + (refundment_type == 1 ? "执行单号" : "合同号") + "</td><td>客户名称</td><td>已付款金额</td><td>已收票金额</td><td>退款金额</td></tr>");
					}
					var pid = "#pid_" + selpcids[i];
					var cusname = "#cusname_" + selpcids[i];
					var amount = "#amount_" + selpcids[i];
					var invoice_amount = "#invoice_amount_" + selpcids[i];
					$("#result_show").append("<tr id=\"addtr_" + selpcids[i] + "\"><td><img src=\"" + base_url + "images/close.png\" onclick=\"pidmove('" + selpcids[i] + "');\"/></td><td>" + $(pid).html() + "</td><td>" + $(cusname).html() + "</td><td>" + $(amount).html() + "</td><td>" + $(invoice_amount).html() + "</td><td><input type=\"text\" class=\"validate[required,max[0],min[-" + $(amount).html() + "]]\" style=\"height:20px;\" name=\"refund_" + selpcids[i] + "\" id=\"refund_" + selpcids[i] + "\" value=\"-" + $(amount).html() + "\"></td><</tr>");
					
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
			data: "action=search_payment&page=" + page + "&refundment_type=" + $('input[name="refund_type"]:checked').val()+ "&search=" + $("#search_pcid").val() + "&cusname=" + $("#cusname").val() + "&t=" + Math.random() + "&vcode=" + vcode,
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

function set_receivables_type(obj){
	if(obj.value == "2"){
		//转账
		$("#nimbank_id").addClass("validate[required]");
	}else{
		//现金
		$("#nimbank_id").removeClass("validate[required]");
	}
	
}
</script>
</body>
</html>
