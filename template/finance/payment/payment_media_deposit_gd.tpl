<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理</title>
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
		<div class="crumbs">财务管理 - 媒体付款申请</div>
		<div class="tab">
      		<ul>
      			<li class="on"><a>归档媒体批量保证金付款申请</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=media_deposit_manager">媒体批量保证金付款申请列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                	<td style="font-weight:bold">发起时间</td>
                	<td>[ADDTIME]</td>
                </tr>
       			<tr>
          			<td style="font-weight:bold; width:150px">媒体名称</td>
          			<td>
            			[MEDIANAME]
          			</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">申请付款时间</td>
          			<td>[PAYMENTDATE]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">最后申请提交时间</td>
          			<td>[PAYMENTAPPLYDEADLINE]</td>
        		</tr>
      			<tr>
          			<td style="font-weight:bold;">应付金额</td>
          			<td>
          				[PAYMENTAMOUNTPLAN] 元
          			</td>
       	 		</tr>
      			<tr>
          			<td style="font-weight:bold;width:150px">实付金额</td>
          			<td><b><span style="color:#ff9933; font-size:14px" id="payment_amount_real">[PAYMENTAMOUNTREAL]</span></b> 元</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">返点金额</td>
          			<td>[REBATEAMOUNT] 元</td>
          		</tr>
      		</table>
      		<p/>
      		<table  id="paymentdg"></table>
      		<p></p>
				<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
					<tr><td style="font-weight:bold; width:150px">付款时间</td><td><input type="text" name="paymentdate" id="paymentdate" onclick="WdatePicker()" class="text Wdate" readonly="readonly"/></td></tr>
					<tr><td style="font-weight:bold; width:150px">付款金额</td><td><input type="text" name="paymentamount" class="text"/></td></tr>
					<tr><td style="font-weight:bold; width:150px">付款方式</td><td><input type="radio" name="paymenttype" value="1" checked/>&nbsp;现金支付&nbsp;&nbsp;<input type="radio" name="paymenttype" value="2"/>&nbsp;银行转账&nbsp;&nbsp;<select name="paymentbank">[NIMBANKS]</select></td></tr>
				</table>
				<div class="btn_div">
        		<input type="hidden" name="payment_id" id="payment_id" value="[PAYMENTID]"/><input type="hidden" name="id" id="id" value="[APPLYID]"/><input type="hidden" name="gdvalues" id="gdvalues"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="payment_media_deposit_gd"/><input type="button" value="提交" class="btn_sub" id="submitb" name="subbtn"/>
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
	$("#paymentdg").datagrid({
		url:base_url + "get_data.php?action=getMediaDepositPaymentGD&apply_id=" + encodeURI($("#id").val()),
		height:"auto",
		title:"已付款记录",
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		pagination:true,
		columns:[[
		 {field:'paymentdate',width:100,title:"付款时间"},
         {field:'paymentamount',width:200,title:"实付金额"},
          {field:'paymentype',width:200,title:"付款方式"},
         {field:'paymentbank',width:200,title:"付款行"}
          ]]
	});
	
	$("#sub").click(function(){
		$("#audit_result").val("1");
		$("#formID").submit();
	});

	$("#auditsub").click(function(){
		$("#audit_result").val("2");
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
</script>
</body>
</html>
