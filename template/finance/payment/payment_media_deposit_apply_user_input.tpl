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
		<div class="crumbs">财务管理 - 付款申请</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>分配合同</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=my_media_deposit_assigned&id=[ID]">已分配记录</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=media_apply_deposit_user_assignlist">待输入批量付保证金列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
    		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
    			<tr>
    				<td style="font-weight:bold;">媒体名称</td><td>[MEDIANAME]</td>
    				<td style="font-weight:bold;">付款日期</td><td>[PAYMENTDATE]</td>
    				<td style="font-weight:bold;">最后申请提交时间</td><td>[PAYMENTDEADLINE]</td>
    				<td><input type="button" value="展开" id="openbtn" class="btn"/></td>
    			</tr>
    			<tr id="showtr">
    				<td colspan="7">[ITEMLIST]</td>
    			</tr>
    		</table>
    		<p/>
			<table id="itemdg"></table>
			
			<div id="itemtoolbar" style="padding:5px;height:auto">
				<div style="margin-bottom:5px">
					<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="itemcancelbtn">删除</a>
				</div>
			</div>
    		<p/>
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
          		<tr>
          			<td style="font-weight:bold;">客户名称</td><td><input type="text" style="height:20px;" id="cusname" name="cusname"/></td>
          			<td style="font-weight:bold;">合同号</td><td><input type="text" style="height:20px;" id="cid" name="cid"/></td>
          			<td colspan="2"><input type="button" value="搜索" class="btn" id="sbtn"/></td>
        		</tr>
        		<tr><td id="search_result" colspan="8"></td></tr>
      		</table>
      		<p/>
      		<table id="dg"></table>
			<!--table class="easyui-datagrid" id="dg" data-options="
      			title:'所选媒体数据',
      			autoRowHeight:true,
      			striped:true,
      			rownumbers:true,
      			toolbar:'#toolbar'">
      			<thead>
					<tr>
						<th data-options="field:'x',checkbox:true" rowspan="2"></th>
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
						<th data-options="field:'k',width:300,align:'right'" rowspan="2">本次申请金额</th>
						<th data-options="field:'l',width:200,align:'right'" rowspan="2">已付成本合计金额</th>
						<th data-options="field:'m',width:400,align:'right'" rowspan="2">返点抵扣</th>
						<th colspan="3">返点</th>
						<th data-options="field:'q',width:200,align:'right'" rowspan="2">虚拟发票合计金额</th>
						<th data-options="field:'r',width:200,align:'right'" rowspan="2">真实发票到票合计金额</th>
						<th data-options="field:'s',width:200,align:'right'" rowspan="2" >已付款未到票金额</th>
						<th data-options="field:'t',width:400,align:'right'" rowspan="2">个人借款抵扣</th>
						<th data-options="field:'u',width:200,align:'right'" rowspan="2">实付金额</th>
						<th data-options="field:'v',width:450" rowspan="2" >是否垫付</th>
					</tr>
            		<tr>
			        	<th data-options="field:'n',width:200,align:'right'">待开票返点</th>
			        	<th data-options="field:'o',width:200,align:'right'">已开票返点</th>
			           	<th data-options="field:'p',width:200,align:'right'">无需开票返点</th>
			        </tr>
       			 </thead>		   
				</table-->
				<div id="toolbar" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn">删除</a>
					</div>
				</div>
				
      		<div class="btn_div">
        		<input type="hidden" name="id" id="id" value="[ID]"/><input type="hidden" name="assignid" id="assignid" value="[ASSIGNID]"></input><input type="hidden" name="itemids" id="itemids" value=","/><input type="hidden" name="pids" id="pids" value="," /><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="payment_media_deposit_apply_user_input"/><!--input type="submit" value="保 存" class="btn_sub" id="save" />&nbsp;&nbsp;--><input type="submit" value="提 交" class="btn_sub" id="submit" />
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

	$("#fpbtn").click(function(){
		$('input[name="itemselect"]:checked').each(function(){
			var itemids = $("#itemids").val();
			if(itemids.indexOf("," + $(this).val() + ",") == -1){
				var js = new Array();
				js['xx'] = $(this).val();
				js['aa'] = $("#ggz_" + $(this).val()).html();
				js['bb'] = $("#mthth_" + $(this).val()).html();
				js['cc'] = $("#sqje_" + $(this).val()).html();
				js['dd'] = $("#htfkrq_" + $(this).val()).html();
				js['ee'] = $("#kjje_" + $(this).val()).html();
				js['ff'] = $("#kjkssj_" + $(this).val()).html();

				 $('#itemdg').datagrid('appendRow',js);

				 $("#itemids").val(itemids + $(this).val() + ",");
			}
		});
	});

	$("#itemcancelbtn").click(function(){
		var rows = $('#itemdg').datagrid('getSelections');
		if(rows.length >0){
			for(var i = 0;i<rows.length;i++){
				//dg中删除行
				var index = $('#itemdg').datagrid('getRowIndex', rows[i]);
				$('#itemdg').datagrid('deleteRow', index);  

				var itemids = $("#itemids").val();
				$("#itemids").val(itemids.replace("," + rows[i].xx + ",",","));
			}
		}
	});
	
	$("#itemdg").datagrid({
		title:'所选对账单数据',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		toolbar:"#itemtoolbar",
		columns:
			[[
				{field:'xx',checkbox:true},
				{field:'aa',width:200,title:"广告主"},
				{field:'bb',width:200,title:"媒体合同号"},
				 {field:'cc',width:200,align:'right',title:"本次申请保证金金额"},
				{field:'dd',width:200,title:"合同付款日期"},
				{field:'ee',width:200,align:'right',title:"框架金额"},
				{field:'ff',width:200,title:"框架开始日期"}
			]]
	});

	$("#dg").datagrid({
		title:'所选媒体数据',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		toolbar:'#toolbar',
		columns:
			[[
				{field:'x',checkbox:true},
				{field:'a',width:200,title:"合同号"},
				{field:'b',width:200,title:"客户名称"},
				{field:'c',width:200,align:'right',title:"客户到保证金金额"},
				{field:'d',width:200,align:'right',title:"已付媒体保证金金额"},
				{field:'e',width:400,align:'right',title:"本次申请金额"},
				{field:'f',width:400,align:'right',title:"个人借款抵扣"},
				{field:'g',width:400,title:"是否垫付"},
				{field:'h',width:200,align:'right',title:"实付金额"}
			]]
	});
	
	
	$("#showtr").hide();
	
	$("#openbtn").click(function(){
		$("#showtr").toggle();
	});
	
	
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

	$("#sbtn").click(function(){
		 dosearch(1);
	});

	$("#cancelbtn").click(function(){
		var rows = $('#dg').datagrid('getSelections');
		if(rows.length >0){
			for(var i = 0;i<rows.length;i++){
				//dg中删除行
				var index = $('#dg').datagrid('getRowIndex', rows[i]);
				$('#dg').datagrid('deleteRow', index);  

				var nowpids = $("#pids").val();
				$("#pids").val(nowpids.replace("," + rows[i].x + ",",","));
			}
		}
	});
});

function pass_statement(id){
	var trid = "statetr_" + id;
	$("#" + trid).remove();
}

function dosearch(page){
	var cid = $.trim($("#cid").val());
	var cusname =  $.trim($("#cusname").val());

	if(cid == "" &&  cusname=="" ){
		alert("请至少输入一个搜索条件");
	}else{
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_simple_contract&page=" + page + "&cid=" + cid + "&cusname=" + cusname +  "&t=" + Math.random() + "&vcode=" + vcode,
			dataType:'text',
			async: false,
			success: function(msg){
				$("#search_result").empty();
				$("#search_result").append(msg);
			},
		 	error: function(e){
		 		alert("搜索合同记录异常");
		 	}
		});

	}
}

function pidmove(){
	var sel = false;
	var newadd = new Array();
	$('input[name="sel"]:checked').each(function(){
			var nowpids = $("#pids").val();
			if(nowpids.indexOf("," + $(this).val() + ",") == -1){
				if(!sel){
					sel = true;
				}
				$("#pids").val(nowpids + $(this).val() + ",");
				newadd.push($(this).val());
			}
	});

	if(sel){
		//newadd值去查找相关数据
		$.ajax({
			   type: "post",
			   url: base_url + "get.php",
			   cache:"false",
			   data: "action=get_payment_media_deposit_apply_cidinfo_search&cids=" + newadd.join(",") + "&t=" + Math.random() + "&vcode=" + vcode,
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
		
	}else{
		alert("合同选择数据不能为空或者选择了已有的媒体数据");
	}
}

function openit(pid){
	var id = "tr_" + pid;
	$("#" + id).toggle();
}

function countItemAmount(obj){
	var val = obj.value;
	if(!isNaN(val)){
		var id = obj.id;
		id = id.split("_");
		var len = id.length;

		var apply_amount_id = "payment_amount_" + id[len-2] + "_" + id[len-1];
		var rebate_amount_id = "rebate_deduction_amount_" + id[len-2] + "_" + id[len-1];
		var person_loan_amount_id = "person_loan_amount_" +  id[len-2] + "_" + id[len-1];

		var real_amount = $("#" + apply_amount_id).val() - $("#" + rebate_amount_id).val() - $("#" + person_loan_amount_id).val();
		var u_id = "u_" + id[len-2] + "_" + id[len-1];
		$("#" + u_id).html(real_amount);
	}else{
		alert("非有效数字");
		obj.focus();
	}
}

function doNimPayFirst(obj){
	var id = obj.id;
	id = id.split("_");
	var len = id.length;
	
	var u_id = "u_" + id[len-2] + "_" + id[len-1];
	var pay = "nim_pay_first_amount_" + id[len-2] + "_" + id[len-1];
	if(obj.checked){
		//垫付
		$("#" + pay).val($("#" + u_id).html());
	}else{
		//非垫付
		$("#" + pay).val("0");
	}
	
}
</script>
</body>
</html>
