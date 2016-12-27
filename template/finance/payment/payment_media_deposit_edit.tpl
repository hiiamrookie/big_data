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
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 媒体付款申请</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>修改媒体保证金付款申请</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=my_media_deposit_apply_list">已申请媒体保证金付款列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
       			<tr>
                	<td style="font-weight:bold">应用流程</td>
                	<td>[PROCESSLIST]</td>
                </tr>
       			<tr>
          			<td style="font-weight:bold; width:150px">媒体名称</td>
          			<td>
            			<input type="text" class="validate[required,maxSize[255]] " style="height:20px;width:300px;" id="media_name" name="media_name" value="[MEDIANAME]"/>
          			</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold; width:150px">开户行</td>
          			<td>
            			<select name="bank_name_select" id="bank_name_select" style="width:300px;" class="validate[groupRequired[bankname]] select"><option value="">请选择开户行</option>[BANKLIST]</select>&nbsp;&nbsp;或输入&nbsp;<input type="text" class="validate[groupRequired[bankname],maxSize[255]]" style="height:20px;width:300px;" id="bank_name" name="bank_name"/>
          			</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold; width:150px">银行账号</td>
          			<td>
            			<select name="bank_account_select" id="bank_account_select" style="width:300px;" class="validate[groupRequired[bankaccount]] select"><option value="">请选择银行账号</option>[ACCOUNTLIST]</select>&nbsp;&nbsp;或输入&nbsp;<input type="text" class="validate[groupRequired[bankaccount],maxSize[255]]" style="height:20px;width:300px;" id="bank_account" name="bank_account"/>
          			</td>
        		</tr>
      			<tr>
          			<td style="font-weight:bold;">应付金额</td>
          			<td>
          				<input type="text" class="validate[required]" style="height:20px;" id="payment_amount_plan" name="payment_amount_plan" value="[PAYMENTAMOUNTPLAN]" onblur="javascript:countpaymentreal();"/> 元
          			</td>
       	 		</tr>
      			<tr>
          			<td style="font-weight:bold;width:150px">付款时间</td>
          			<td><input type="text" id="payment_date" name="payment_date" onclick="WdatePicker({minDate:'%y-%M-%d'});" class="validate[required] text Wdate" readonly="readonly" value="[PAYMENTDATE]"/></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">是否垫付</td>
          			<td><input type="checkbox" name="is_nim_pay_first"  id="is_nim_pay_first" value="1" [ISNIMPAYFIRST]/>&nbsp;是</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">返点抵扣</td>
          			<td><input type="checkbox" name="is_rebate_deduction" id="is_rebate_deduction" value="1" [ISREBATEDEDUCTION] onclick="javascript:countAgain(this);"/>&nbsp;是</td>
          		</tr>
          		<tr>
          			<td style="font-weight:bold;width:150px">返点金额</td>
          			<td><input type="text" name="rebate_amount" id="rebate_amount" style="height:20px;" class="validate[optional,custom[money]]" value="[REBATEAMOUNT]" onblur="javascript:countpaymentreal();"/> 元</td>
          		</tr>
          		<tr>
          			<td style="font-weight:bold;width:150px">返点抵扣附件上传</td>
          			<td>
          				<div>
                    		<input type="file" name="upfile" id="upfile" size="40" style="height:20px;"/>&nbsp;
                    		<input type="button" id="upload" value="上 传" onclick="up_uploadfile(this,'rebate_dids',0,0);" class="btn"/><input type="hidden" name="rebate_dids" id="rebate_dids" size="50" value="[REBATEDIDSVALUE]"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font>
						</div>
						 [REBATEDIDS]
					</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">保证金抵扣</td>
          			<td><input type="checkbox" name="is_deposit_deduction" id="is_deposit_deduction" value="1" [ISDEPOSITDEDUCTION]/>&nbsp;是</td>
          		</tr>
          		<tr>
          			<td style="font-weight:bold;width:150px">保证金抵扣附件上传</td>
          			<td>
						<div>
                    		<input type="file" name="depositupfile" id="depositupfile" size="40" style="height:20px;"/>&nbsp;
                    		<input type="button" id="upload2" value="上 传" onclick="up_uploadfile(this,'deposit_dids',0,0);" class="btn"/><input type="hidden" name="deposit_dids" id="deposit_dids" size="50" value="[DEPOSITDIDSVALUE]"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font>
						</div>
						[DEPOSITDIDS]
					</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">个人借款抵扣</td>
          			<td><input type="checkbox" name="is_person_loan_deduction" id="is_person_loan_deduction" value="1" [ISPERSONLOANDEDUCTION] onclick="javascript:countAgain(this);"/>&nbsp;是</td>
        		</tr>
        		<tr>
        			<td style="font-weight:bold;width:150px">个人借款金额</td>
        			<td><input type="text" name="person_loan_amount" id="person_loan_amount" style="height:20px;" class="validate[optional,custom[money]]" value="[PERSONLOANAMOUNT]" onblur="javascript:countpaymentreal();"/> 元</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">实付金额</td>
          			<td><b><span style="color:#ff9933; font-size:14px" id="actually_paid">[PAYMENTAMOUNTREAL]</span></b> 元</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">备注</td>
          			<td><textarea id="remark" class="validate[optional,maxSize[1000]] textarea" name="remark" rows="3" style="width:400px;height:80px">[REMARK]</textarea></td>
        		</tr>
       	 		<tr>
          			<td style="font-weight:bold;width:150px">最后申请提交时间</td>
          			<td><input type="text" id="payment_apply_deadline" name="payment_apply_deadline" onclick="WdatePicker({minDate:'%y-%M-%d'});" class="validate[required] text Wdate" readonly="readonly" value="[PAYMENTAPPLYDEADLINE]"/></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold">对账单</td>
          			<td>
						<div>
                    		<input type="file" name="statementupfile" id="statementupfile" size="40" style="height:20px;"/>&nbsp;
                    		<input type="button" id="upload3" value="上 传" onclick="up_uploadfile(this,'statement',1,1);" class="btn"/>&nbsp;&nbsp;<input type="button" value="&nbsp;下载模板&nbsp;" id="dt" class="longbtn"/><input type="hidden" name="statement" id="statement" size="50" value="[STATEMENTDIDSVALUE]"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_EXCEL_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font>
						</div>
						[STATEMENTDIDS]
					</td>
				</tr>
				<tr>
					<td style="font-weight:bold">对账单记录</td>
					<td><input type="button" value="展开" class="btn" id="statebtn"/></td>
				</tr>
				<tr id="stateshow">
					<td colspan="2">
					[STATESHOW]
					</td>
				</tr>
				<tr>
          			<td style="font-weight:bold;width:50px;">搜索用户</td>
          			<td><input type="text" id="searchuser" name="searchuser" style="height:20px;width:196px;"/>&nbsp;&nbsp;<input type="button" class="btn" value="搜索" id="searchbtn"/>&nbsp;&nbsp;<input type="button" class="btn" value="添加" id="addbtn"/></td>
        		</tr>
        		<tr>
        			<td>&nbsp;</td>
        			<td id="searchlist"></td>
        		</tr>
        		<tr>
        			<td style="font-weight:bold;width:50px;">选择以下用户分配执行单<input type="hidden" value="[USERS]" name="users" id="users"/></td>
        			<td id="userlist">[USERLIST]</td>
        		</tr>
      		</table>
      		<div class="btn_div">
        		<input type="hidden" name="statement_del" id="statement_del"/><input type="hidden" name="id" value="[ID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="payment_media_deposit_edit"/><input type="button" value="提 交" class="btn_sub" id="sub" name="sub"/>
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
var user_array = [JSUSERARRAY];
var user_show_array = [JSUSERSHOWARRAY];
var statement_del = new Array();
$(document).ready(function() {
	$("#stateshow").hide();

	$("#statebtn").click(function(){
		$("#stateshow").show();
	});
	
	$("#dt").click(function(){
		location.href= base_url + "download_template.php?type=payment_media_deposit_statement";
	});
	
	$("#sub").click(function(){
		$("#formID").submit();
	});
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});
	
	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true)	$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});

	
	$("#searchbtn").click(function(){
		if($.trim($("#searchuser").val()) == ""){
			alert("请输入用户名或者姓名的关键字");
		}else{
			$.ajax({
				   type: "POST",
				   url: "do.php",
				   cache:false,
				   data: "action=getuser&q=" + $("#searchuser").val() + "&t=" + Math.random() + "&vcode=" + vcode,
				   dataType:'text',
				   async: false,
				   success: function(msg){
					   $("#searchlist").html(msg);
				   },
			 	   error: function(e){
			 		   alert("搜索用户信息出错");
			 	   }
			});
		}
	});

	$("#addbtn").click(function(){
		$("input[name='checkuser[]']").each(function(){
			if($(this).attr("checked")=="checked"){
				 var has_val = false;
				  for(var i=0;i<user_array.length;i++){
					  if(user_array[i] == $(this).val()){
						  has_val = true;
						  break;
					  } 
				  }
				  if(!has_val){
					  user_array.push($(this).val()); 
					  var uid = "usershow_" + $(this).val();
					  user_show_array.push($("#" + uid).html()  + "<img src=\"" + base_url + "images/close.png\" onclick=\"user_del(" + $(this).val()+ ")\"/>");
				  }
			}
		});

		show_user();
	});
	
	$("#media_name").blur(function(){
		$("#bank_name_select").empty();
		$("#bank_name_select").append("<option value=\"\">请选择开户行</option>");
		$("#bank_account_select").empty();
		$("#bank_account_select").append("<option value=\"\">请选择银行账号</option>");
		if($(this).val() != ""){	
			$.ajax({
				type: "POST",
				url: "do.php",
				cache:"false",
				data: "action=search_media_bank&media_name=" + $(this).val() + "&t=" + Math.random() + "&vcode=" + vcode,
				dataType:'text',
				async: false,
				success: function(msg){
					if(msg!=""){
						$("#bank_name_select").append(msg);
					}
				},
				 error: function(e){
				 	alert("所获取媒体开户行出错");
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
				data: "action=search_media_bank_account&media_name=" + $("#media_name").val() + "&bank_name=" + $(this).val() + "&t=" + Math.random() + "&vcode=" + vcode,
				dataType:'text',
				async: false,
				success: function(msg){
					if(msg!=""){
						$("#bank_account_select").append(msg);
					}
				},
				 error: function(e){
				 	alert("所获取媒体银行账号出错");
				 }
			});
		}
	});
});

function newremove(arr,dx){
	var newarr = new Array();
	for(var i=0;i<arr.length;i++){
		if(i!=dx){
			newarr.push(arr[i]);
		}
	}
	return newarr;
}
function user_del(id){
	var dx = -1;
	for(var i=0;i<user_array.length;i++){
		if(user_array[i] == id){
			dx = i;
			break;
		}
	}
	
	user_array = newremove(user_array,dx);
	user_show_array = newremove(user_show_array,dx);
	show_user();
}

function show_user(){
	if(user_array.length>0){
		$("#users").val("," + user_array.join(",") + ",");
	}else{
		$("#users").val(",");
	}
	$("#userlist").html(user_show_array.join(","));	
}

function delete_statement(id){
	if(window.confirm("确认删除该条目？")){
		statement_del.push(id);
		var nid = "statetr_" + id;
		$("#" + nid).remove();
	}
	$("#statement_del").val(statement_del.join(","));
}

function countAgain(obj){
	var id = obj.id;
	if(id ==  "is_rebate_deduction"){
		var v = "rebate_amount";
	}else if(id == "is_person_loan_deduction"){
		var v = "person_loan_amount";
	}
	if(obj.checked){
		$("#" + v).removeClass("validate[optional,custom[money]]");
		$("#" + v).addClass("validate[required,custom[money]]");
	}else{
		$("#" + v).val("");
		$("#" + v).removeClass("validate[required,custom[money]]");
		$("#" + v).addClass("validate[optional,custom[money]]");
	}
	countpaymentreal();
}

function countpaymentreal(){
	var payment_amount_plan = $.trim($("#payment_amount_plan").val()) == "" ? 0 : $.trim($("#payment_amount_plan").val());
	var rebate_amount = $("#is_rebate_deduction").attr("checked") == "checked" ? ($.trim($("#rebate_amount").val()) == "" ? 0 : $.trim($("#rebate_amount").val())) : 0;
	var person_loan_amount = $("#is_person_loan_deduction").attr("checked") == "checked" ? ($.trim($("#person_loan_amount").val()) == "" ? 0 : $.trim($("#person_loan_amount").val())) : 0;
	var deposit_amount = 0;
	var errs = new Array();
	if(isNaN(payment_amount_plan)){
		errs.push("应付金额必须是数字");
	}
	if(isNaN(rebate_amount)){
		errs.push("返点金额必须是数字");
	}
	if(isNaN(person_loan_amount)){
		errs.push("个人借款金额必须是数字");
	}
	if(errs.length>0){
		$("#actually_paid").html("0.00");
		alert(errs.join("\n"));
	}else{
		var real_amount = Number(payment_amount_plan) - Number(rebate_amount) -Number( person_loan_amount) - Number(deposit_amount);
		$("#actually_paid").html(real_amount.toFixed(2));
	}

}
</script>
</body>
</html>
