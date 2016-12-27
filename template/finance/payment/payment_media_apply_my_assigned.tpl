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
        		<li><a href="[BASE_URL]finance/payment/?o=payment_userinput&id=[ASSIGNID]">分配执行单</a></li>
        		<li class="on"><a>已分配记录</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=media_apply_user_assignlist">待输入批量付合同款列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
			<table id="itemdg" style="width:100%"></table>		
			<!--div id="itemtoolbar" style="padding:5px;height:auto">
				<div style="margin-bottom:5px">
					<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="itemcancelbtn">删除</a>
				</div>
			</div-->
      		<p/>
			<table class="easyui-datagrid" id="dg" data-options="
      			title:'所选媒体数据',
      			url:'[BASE_URL]get_data.php?action=getMediaPaymentAssignedPid&apply_id=[ID]',
      			autoRowHeight:true,
      			striped:true,
      			rownumbers:true,
      			width:'98.5%'">
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
				</table>
				<!--div id="toolbar" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn">删除</a>
					</div>
				</div-->
				
      		<div class="btn_div">
        		<input type="hidden" name="id" id="id" value="[ID]"/><input type="hidden" name="assignid" id="assignid" value="[ASSIGNID]"></input><input type="hidden" name="itemids" id="itemids" value=","/><input type="hidden" name="pids" id="pids" value="," /><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="payment_media_apply_user_input"/><!--input type="submit" value="保 存" class="btn_sub" id="save" />&nbsp;&nbsp;><input type="submit" value="提 交" class="btn_sub" id="submit" /-->
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
	$("#itemdg").datagrid({
		title:'所选对账单数据',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		//toolbar:"#itemtoolbar",
		url:base_url + "get_data.php?action=getMediaPaymentItems&apply_id=[ID]",
		columns:
			[[
				//{field:'xx',checkbox:true},
				{field:'aa',width:300,title:"广告主"},
				{field:'bb',width:100,title:"产品"},
				 {field:'cc',width:100,align:'right',title:"合同付款额"},
				{field:'dd',width:100,title:"充值日期"},
				{field:'ee',width:100,title:"品牌"},
				//{field:'ff',width:100,align:'right',title:"原合同总额"},
				//{field:'gg',width:100,title:"产品"},
				//{field:'hh',width:100,align:'center',title:"上线日期"},
				//{field:'ii',width:100,align:'center',title:"下线日期"},
			]]
	});

	
	//$("#showtr").hide();
	
	//$("#openbtn").click(function(){
	//	$("#showtr").toggle();
	//});
	
	//$("#search").autocomplete(base_url + "executive/?o=getpidname", { width: 300, max: 50 });
	
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
	var pid = $.trim($("#pid").val());
	var projectname =  $.trim($("#projectname").val());
	var cusname =  $.trim($("#cusname").val());
	var medianame =  $.trim($("#medianame").val());
	var starttime =  $.trim($("#starttime").val());
	var endtime =  $.trim($("#endtime").val());
	var mediacontractnumber =  $.trim($("#mediacontractnumber").val());
	var planpaytime =  $.trim($("#planpaytime").val());

	if(pid == "" && projectname=="" && cusname=="" && medianame=="" && starttime=="" && endtime == "" && mediacontractnumber=="" && planpaytime==""){
		alert("请至少输入一个搜索条件");
	}else{
		
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_executive&page=" + page + "&search=" + pid + "&cusname=" + cusname + "&projectname=" + projectname + "&medianame=" + medianame + "&t=" + Math.random() + "&vcode=" + vcode,
			dataType:'text',
			async: false,
			success: function(msg){
				$("#search_result").empty();
				$("#search_result").append(msg);
			},
		 	error: function(e){
		 		alert("搜索执行单记录异常");
		 	}
		});

	}
}

function pidmove(){
	var sel = false;
	var newadd = new Array();
	$('input[name="abc"]:checked').each(function(){
	//$(":checkbox").each(function(index, element) {
		//if ($(this).attr("checked")=="checked" && $(this).val() !="1"){
			var nowpids = $("#pids").val();
			if(nowpids.indexOf("," + $(this).val() + ",") == -1){
				if(!sel){
					sel = true;
				}
				$("#pids").val(nowpids + $(this).val() + ",");
				newadd.push($(this).val());
			}
		//}
	});

	if(sel){
		//newadd值去查找相关数据
		$.ajax({
			   type: "post",
			   url: base_url + "get.php",
			   cache:"false",
			   data: "action=get_payment_person_apply_pidinfo_search&pids=" + newadd.join(",") + "&t=" + Math.random() + "&vcode=" + vcode,
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
		alert("媒体选择数据不能为空或者选择了已有的媒体数据");
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