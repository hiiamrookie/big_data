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
      			<li class="on"><a>审核媒体批量付款申请</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=media_manager">媒体批量付款申请列表</a></li>
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
        		<!-- tr>
        			<td style="font-weight:bold;width:50px;">审核留言</td>
        			<td><textarea id="audit_content" class="validate[optional,maxSize[1000]] textarea" name="audit_content" rows="3" style="width:400px;height:80px"></textarea></td>
        		</tr-->
      		</table>
      		<p/>
      		<table id="itemdg" style="width:100%"></table>		
			<!--div id="itemtoolbar" style="padding:5px;height:auto">
				<div style="margin-bottom:5px">
					<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="itemcancelbtn">删除</a>
				</div>
			</div-->
      		<p/>
			<table class="easyui-datagrid" id="dg" data-options="
      			title:'所选媒体数据',
      			url:'[BASE_URL]get_data.php?action=getMediaPaymentUserAssignedPid&apply_id=[ID]&uid=[UID]',
      			autoRowHeight:true,
      			striped:true,
      			rownumbers:true,
      			singleSelect:true,
      			width:'100%'">
      			<thead>
					<tr>
						<!--th data-options="field:'x',checkbox:true" rowspan="2"></th-->
		                <th data-options="field:'a',width:100" rowspan="2">执行单号</th>
		                <th data-options="field:'b',width:200" rowspan="2">客户名称</th>
		                 <th data-options="field:'c',width:200" rowspan="2">项目名称</th>
		                <th data-options="field:'d',width:200,align:'right'" rowspan="2">客户执行收入</th>
		                <th data-options="field:'e',width:200,align:'right'" rowspan="2">已收客户款合计金额</th>
		                <th data-options="field:'f',width:200,align:'right'" rowspan="2">已开票合计金额</th>
		                <th data-options="field:'g',width:200,align:'right'" rowspan="2">已执行未到客户款金额</th>
		                <th data-options="field:'h',width:200" rowspan="2">供应商</th>
						<th data-options="field:'i',width:200,align:'right'" rowspan="2">媒体执行成本</th>
						 <th data-options="field:'j',width:200,align:'right'" rowspan="2">已执行未付成本金额</th>
						<th data-options="field:'k',width:200,align:'right'" rowspan="2">本次申请金额</th>
						<th data-options="field:'l',width:200,align:'right'" rowspan="2">已付成本合计金额</th>
						<th data-options="field:'m',width:200,align:'right'" rowspan="2">返点抵扣</th>
						<th colspan="3">返点</th>
						<th data-options="field:'q',width:200,align:'right'" rowspan="2">虚拟发票合计金额</th>
						<th data-options="field:'r',width:200,align:'right'" rowspan="2">真实发票到票合计金额</th>
						<th data-options="field:'s',width:200,align:'right'" rowspan="2" >已付款未到票金额</th>
						<th data-options="field:'t',width:200,align:'right'" rowspan="2">个人借款抵扣</th>
						<th data-options="field:'u',width:200,align:'right'" rowspan="2">实付金额</th>
						<th data-options="field:'v',width:150" rowspan="2" >是否垫付</th>
						<th data-options="field:'w',width:450" rowspan="2" >状态</th>
					</tr>
            		<tr>
			        	<th data-options="field:'n',width:200,align:'right'">待开票返点</th>
			        	<th data-options="field:'o',width:200,align:'right'">已开票返点</th>
			           	<th data-options="field:'p',width:200,align:'right'">无需开票返点</th>
			        </tr>
       			 </thead>		   
				</table>
      		<!--div class="btn_div">
        		<input type="hidden" name="audit_result" id="audit_result" value="1"/><input type="hidden" name="statement_del" id="statement_del"/><input type="hidden" name="id" value="[ID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="payment_media_audit"/><input type="button" value="审核通过" class="btn_sub" id="sub" name="sub"/>&nbsp;&nbsp;<input type="button" value="审核驳回" class="btn_sub" id="auditsub" name="auditsub"/>
      		</div-->
      		<p/>
      		<div>
	      		<table>
					<tr>
						<td style="font-weight:bold;width:100px">审核意见</td>
						<td><textarea id="remark" class="validate[optional,maxSize[1000]] textarea" name="remark" rows="3" style="width:400px;height:80px"></textarea></td>
					</tr>
				</table>
			</div>
      		<div class="btn_div">
      			<input type="hidden" name="id" id="id" value="[ID]"/><input type="hidden" name="uid" id="uid" value="[UID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="auditvalue" id="auditvalue" value="pass"/><input type="hidden" name="action" id="action" value="audit_full_payment_media"/><input type="button" value="审核通过" class="btn_sub" id="submitb" name="subbtn"/>&nbsp;<input type="button" value="审核驳回" class="btn_sub" id="rejectb" name="rejbtn"/>
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
		if(window.confirm("确认审核驳回该付款申请条目？")){
			$("#auditvalue").val("reject");
			$("#formID").submit();
		}
	});
	
	$("#itemdg").datagrid({
		title:'所选对账单数据',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		//toolbar:"#itemtoolbar",
		url:base_url + "get_data.php?action=getMediaPaymentItemsUserAssigned&apply_id=" + $("#id").val() + "&uid=" + $("#uid").val(),
		columns:
			[[
				/*
				{field:'aa',width:300,title:"广告主"},
				{field:'bb',width:100,title:"合同号"},
				 {field:'cc',width:100,align:'right',title:"合同付款额"},
				{field:'dd',width:100,align:'center',title:"合同付款日期"},
				{field:'ee',width:100,title:"品牌"},
				{field:'ff',width:100,align:'right',title:"原合同总额"},
				{field:'gg',width:100,title:"产品"},
				{field:'hh',width:100,align:'center',title:"上线日期"},
				{field:'ii',width:100,align:'center',title:"下线日期"},
				*/
				{field:'aa',width:300,title:"广告主"},
				{field:'bb',width:100,title:"产品"},
				 {field:'cc',width:100,align:'right',title:"合同付款额"},
				{field:'dd',width:100,align:'center',title:"充值日期"},
				{field:'ee',width:100,title:"媒体合同号"}
			]]
	});
	
	$("#stateshow").hide();

	$("#statebtn").click(function(){
		$("#stateshow").toggle();
	});
	
	$("#dt").click(function(){
		location.href= base_url + "download_template.php?type=payment_media_statement";
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

function auditem(listid){
	var sid = "auditsel_" + listid;
	var tid = "auditresaon_" + listid;
	var auditsel = $('input[name="' + sid + '"]:checked').val();
	if(window.confirm("确定" + (auditsel==1 ? "通过" : "驳回") + "该条目？")){
		$.ajax({
			   type: "post",
			   url: base_url + "finance/payment/do.php",
			   cache:"false",
			   data: "action=auditMediaPaymentItem&apply_id=" + $("#id").val() + "&listid=" + listid + "&auditsel=" + auditsel + "&auditresaon=" + $("#" + tid).val() + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				  //	if(msg == "1"){
				///		var id = "auditres_" + payment_list_id;
				//		$("#" + id).html((auditsel =="1" ? "通过" : "驳回"));
				//	}else{
						alert(msg);
				//	}
			   },
		 	   error: function(e){
		 		   alert("提交审核数据异常");
		 	   }
		});
		location.href="";
	}
}
</script>
</body>
</html>
