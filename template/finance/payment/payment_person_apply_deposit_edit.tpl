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
        		<li class="on"><a>变更付保证金申请</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=payment_apply_deposit_mylist">已申请付保证金列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
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
          			<td><b><span style="color:#ff9933; font-size:14px" id="pal">[PAYMENTAMOUNTPLAN]</span></b>
          				<input type="hidden" style="height:20px;" id="payment_amount_plan" name="payment_amount_plan" value="[PAYMENTAMOUNTPLAN]"/> 元
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
          			<td><input type="checkbox" name="is_rebate_deduction" id="is_rebate_deduction" value="1" [ISREBATEDEDUCTION]/>&nbsp;是</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">返点金额</td>
          			<td><b><span style="color:#ff9933; font-size:14px" id="ra">[REBATEAMOUNT]</span></b>
          			<input type="hidden" name="rebate_amount" id="rebate_amount" style="height:20px;" value="[REBATEAMOUNT]"/> 元</td>
        		</tr>
        		<!--tr>
          			<td style="font-weight:bold;width:150px">返点比例</td>
          			<td><input style="height:20px;" type="text" name="rebate_rate" id="rebate_rate" size="6"/>&nbsp;%</td>
        		</tr-->
        		<tr>
          			<td style="font-weight:bold;width:150px">保证金抵扣</td>
          			<td><input type="checkbox" name="is_deposit_deduction" id="is_deposit_deduction" value="1" [ISDEPOSITDEDUCTION]/>&nbsp;是&nbsp;&nbsp;<input type="button" value="选择" class="btn" id="depositbtn"/></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">个人借款抵扣</td>
          			<td><input type="checkbox" name="is_person_loan_deduction" id="is_person_loan_deduction" value="1" [ISPERSONLOANDEDUCTION]/>&nbsp;是</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">个人借款金额</td>
          			<td><b><span style="color:#ff9933; font-size:14px" id="plaa">[PERSONLOANAMOUNT]</span></b>
          			<input type="hidden" name="person_loan_amount" id="person_loan_amount" style="height:20px;" value="[PERSONLOANAMOUNT]" /> 元</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">合同款抵扣</td>
          			<td><input type="checkbox" name="is_contract_deduction" id="is_contract_deduction" value="1" [ISCONTRACTDEDUCTION]/>&nbsp;是&nbsp;&nbsp;<input type="button" value="选择" class="btn" id="contractbtn"/></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">实付金额</td>
          			<td><b><span style="color:#ff9933; font-size:14px" id="actually_paid">[PAYMENTREAL]</span></b> 元</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">备注</td>
          			<td><textarea id="remark" class="validate[optional,maxSize[1000]] textarea" name="remark" rows="3" style="width:400px;height:80px">[REMARK]</textarea></td>
        		</tr>
        		<tr>
                    <td style="font-weight:bold">选择应用流程</td>
                    <td>
                        <ul style="border:0;width:100%;" id="pcidlist">
                        [PROCESSLIST]
                        </ul>
                        <div style="clear:both;padding:4px;border:solid 1px #cecece;background:#efefef;"></div>
                    </td>
                </tr>
        		<tr>
          			<td style="font-weight:bold">搜索相关合同</td>
          			<td>合同号&nbsp;&nbsp;<input type="text"  style="height:20px;" id="cid" name="cid"/>&nbsp;&nbsp;
          			客户名称&nbsp;&nbsp;<input type="text"  style="height:20px;" id="cusname" name="cusname"/>&nbsp;&nbsp;<input type="button" class="btn" value="搜 索" id="sbtn"/></td>
        		</tr>
        		<tr><td id="search_result" colspan="4"></td></tr>
      		</table>
      		<p/>
      		
      		
      		<table class="easyui-datagrid" id="dg" data-options="
      			title:'所选媒体数据',
      			autoRowHeight:true,
      			striped:true,
      			toolbar:'#toolbar'">
      			<thead>
					<tr>
						<th data-options="field:'x',checkbox:true" rowspan="2"></th>
						<th data-options="field:'aa',width:100" rowspan="2">供应商</th>
		                <th data-options="field:'a',width:100" rowspan="2">合同号</th>
		                <th data-options="field:'b',width:200" rowspan="2">客户名称</th>
		                 <th data-options="field:'c',width:200" rowspan="2">已收客户保证金金额</th>
		                <th data-options="field:'d',width:200,align:'right'" rowspan="2">上次已付媒体保证金合计</th>
		                <th data-options="field:'e',width:200,align:'right'" rowspan="2">已付保证金合计</th>
		                <th data-options="field:'f',width:200,align:'right'" rowspan="2">本次申请媒体保证金应付金额</th>
						<th data-options="field:'g',width:400,align:'right'" rowspan="2">返点抵扣</th>
						<th colspan="3">返点</th>
						<th data-options="field:'k',width:400,align:'right'" rowspan="2">个人借款抵扣</th>
						<th data-options="field:'l',width:200,align:'right'" rowspan="2">实付金额</th>
						<th data-options="field:'m',width:450" rowspan="2" >是否垫付</th>
					</tr>
            		<tr>
			        	<th data-options="field:'h',width:200,align:'right'">待开票返点</th>
			        	<th data-options="field:'i',width:200,align:'right'">已开票返点</th>
			           	<th data-options="field:'j',width:200,align:'right'">无需开票返点</th>
			        </tr>
       			 </thead>		   
				</table>
				<div id="toolbar" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn">删除</a>
					</div>
				</div>
			<p/>
			<table id="selectdeposit" style="width: 100%"></table>
			<div id="tb2" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="depositcancelbtn">删除</a>
					</div>
				</div>
      		<div class="btn_div">
        		<input type="hidden" name="deposit_deductiuon" id="deposit_deductiuon" value="[DEPOSITDEDUCTION]"/><input type="hidden" name="id" id="id" value="[APPLYID]"/><input type="hidden" name="pids" id="pids" value="[PIDS]" /><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="edit_payment_deposit_person_apply"/><!--input type="button" value="保 存" class="btn_sub" id="save" name="savebtn"/--><input type="button" value="提 交" class="btn_sub" id="submitb" name="subbtn"/>
      		</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<div id="dd">
	<table id="depositdg"></table>
		<div id="tb" style="padding:5px;height:auto">
			媒体名称&nbsp;<input type="text" id="searchmedianame" class="easyui-textbox" style="width:150px;" data-options="prompt:'媒体名称关键字'"/>&nbsp;&nbsp;
			客户名称&nbsp;<input type="text" id="searchcusname" class="easyui-textbox" style="width:150px;" data-options="prompt:'客户名称关键字'"/>&nbsp;&nbsp;
			合同号&nbsp;<input type="text" id="searchcid" class="easyui-textbox" style="width:150px;" data-options="prompt:'合同号关键字'"/>&nbsp;&nbsp;
			<a href="javascript:void(0);" class="easyui-linkbutton" data-options="iconCls:'icon-search'" id="searchbtn">搜索</a>&nbsp;&nbsp;
			<a href="javascript:void(0);" class="easyui-linkbutton" data-options="iconCls:'icon-add'" id="addbtn">添加</a>
		</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<!--script type="text/javascript" src="[BASE_URL]js/easyui/jquery.min.js"></script-->
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
var rows_result = new Array();
$(document).ready(function() {	
	//查询已有的媒体名称
	$("#media_name").autocomplete(base_url + "finance/payment/?o=getMediaName", { width: 300, max: 50 });
	
	$("#depositcancelbtn").click(function(){
		var rows = $('#selectdeposit').datagrid('getSelections');
		if(rows.length == 0){
			alert("请至选择一条记录");
		}else{
			var deposit_deductiuon = $("#deposit_deductiuon").val();
			for(var i=0;i<rows.length;i++){
				deposit_deductiuon = deposit_deductiuon.replace("," + rows[i].dck + ",",",");
				
				var index = $('#selectdeposit').datagrid('getRowIndex', rows[i]);
				$('#selectdeposit').datagrid('deleteRow', index); 
			}
			$("#deposit_deductiuon").val(deposit_deductiuon);
			countpaymentreal();
		}
	});
	
	$("#selectdeposit").datagrid({
		title: "所选保证金数据",
		toolbar: '#tb2',
	    striped : true,
	    url : base_url + 'get_data.php?action=getDepositDeduction&apply_id=' + $("#id").val() + "&deposit_deductiuon=" + $("#deposit_deductiuon").val() + "&payment_type=2",
	    columns:[[
	        {field:'dck',title:'',checkbox:true},
	        {field:'dcid',title:'合同号',width:200},
	        {field:'dcusname',title:'客户名称',width:200},
	        {field:'dmedia',title:'媒体名称',width:200},
	        {field:'dgd_amount',title:'已付媒体保证金',align:'right',width:200},
	        {field:'ddeduction',title:'使用保证金金额',width:200},
	    ]]
	});
	
	$('#dd').dialog({
	    title: '已付媒体保证金搜索',
	    width: document.body.clientWidth * 2 / 3,
	    height: document.body.clientHeight / 2,
	    closed: true,
	    cache : false,
	    modal : true
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

	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true)	$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});

	$("#searchbtn").click(function(){
		var searchmedianame = $.trim($("#searchmedianame").val());
		var searchcusname= $.trim($("#searchcusname").val());
		var searchcid = $.trim($("#searchcid").val());
		if(searchmedianame == "" && searchcusname == "" && searchcid == ""){
			alert("请输入至少一个搜索条件");
		}else{
			var url = base_url + "get_data.php?action=getDepositPayment&vcode=" + vcode + "&t=" + Math.random();
			if(searchmedianame != ""){
				url += "&searchmedianame=" + encodeURI(searchmedianame);
			}
			if(searchcusname != ""){
				url += "&searchcusname=" + encodeURI(searchcusname);
			}
			if(searchcid != ""){
				url += "&searchcid=" + encodeURI(searchcid);
			}
			$('#dg').datagrid({
				url:url
			});
		}
	});
	
	//提交按钮
	$("#submitb").click(function(){
		//应付金额
		//$("#payment_amount_plan").removeClass("validate[optional,custom[money]]");
		//$("#payment_amount_plan").addClass("validate[required,custom[money]]");

		//付款时间
		//$("#payment_date").addClass("validate[required]");

		//action
		$("#action").val("payment_deposit_person_apply");
		$("#formID").submit();
	});

	//保存按钮
	$("#save").click(function(){
		//应付金额
		//$("#payment_amount_plan").removeClass("validate[required,custom[money]]");
		//$("#payment_amount_plan").addClass("validate[optional,custom[money]]");

		//付款时间
		$("#payment_date").removeClass("validate[required]");

		//action
		$("#action").val("payment_person_apply_temp");
		
		$("#formID").submit();
	});


	
	$("#sbtn").click(function(){
		 dosearch(1);
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

	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"topRight", 
	    scroll:false
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

			countPaymentPlan();
		}else{
			alert("请选择至少一条记录")
		}
	});

	//加载已有数据
	$.ajax({
		   type: "post",
		   url: base_url + "get.php",
		   cache:"false",
		   data: "action=get_payment_deposit_apply_cidinfo_search&pids=" + $("#pids").val() + "&type=" + $("#action").val() + "&apply_id=" + $("#id").val() + "&t=" + Math.random() + "&vcode=" + vcode,
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

function countpaymentreal(){
	var payment_amount_plan = $.trim($("#payment_amount_plan").val()) == "" ? 0 : $.trim($("#payment_amount_plan").val());
	var rebate_amount = $("#is_rebate_deduction").attr("checked") == "checked" ? ($.trim($("#rebate_amount").val()) == "" ? 0 : $.trim($("#rebate_amount").val())) : 0;
	var person_loan_amount = $("#is_person_loan_deduction").attr("checked") == "checked" ? ($.trim($("#person_loan_amount").val()) == "" ? 0 : $.trim($("#person_loan_amount").val())) : 0;

	var deposit_deductiuon = $("#deposit_deductiuon").val();
	deposit_deductiuon = deposit_deductiuon.split(",");
	var deposit_amount = 0;
	var errs = new Array();
	
	for(var i=0;i<deposit_deductiuon.length;i++){
		if(deposit_deductiuon[i] !=""){
			var xid = "ddeduction_" + deposit_deductiuon[i]  ;
			//alert($("#" + xid).val());
			if(isNaN($("#" + xid).val())){
				errs.push("保证金抵扣金额必须是数字");
			}else{
				deposit_amount += Math.abs(Number($("#" + xid).val()));
			}
		}
	}
	
	
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


function dosearch(page){
	if($.trim($("#cid").val())=="" && $.trim($("#cusname").val())==""){
		alert("请至少输入一个搜索条件");
	}else{
		
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_contract&page=" + page + "&cid=" + $.trim($("#cid").val()) + "&cusname=" + $.trim($("#cusname").val()) + "&t=" + Math.random() + "&vcode=" + vcode,
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
	$(":checkbox").each(function(index, element) {
		if ($(this).attr("checked")=="checked" && $(this).val() !="1" && $(this).val() !="on"){
			var nowpids = $("#pids").val();
			if(nowpids.indexOf("," + $(this).val() + ",") == -1){
				if(!sel){
					sel = true;
				}
				$("#pids").val(nowpids + $(this).val() + ",");
				newadd.push($(this).val());
			}
		}
	});

	if(sel){
		//newadd值去查找相关数据
		$.ajax({
			   type: "post",
			   url: base_url + "get.php",
			   cache:"false",
			   data: "action=get_payment_deposit_apply_cidinfo_search&pids=" + newadd.join(",") + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   rows = $.parseJSON(msg);
				   for (var i=0;i<rows.rows.length;i++){
					   $('#dg').datagrid('appendRow',rows.rows[i]);
					}

				   countPaymentPlan();
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


function countPaymentPlan(){
	var pids = $("#pids").val();
	var plan = 0;
	pids = pids.split(",");//payment_amount_11BJ010001-_-!360-_-!
	for(var i=0;i<pids.length;i++){
		if(pids[i] != ""){
			var paid = "payment_amount_" + pids[i];
			if(!isNaN($(document.getElementById(paid)).val())){
				plan += Number($(document.getElementById(paid)).val());
			}
		}
	}
	$("#pal").html(plan);
	$("#payment_amount_plan").val(plan);

	countpaymentreal();
}

function countItemAmount(obj,key){
	/*
	var val = obj.value;
	if(!isNaN(val)){
		var apply_amount_id = "payment_amount_" + key;
		var rebate_amount_id = "rebate_deduction_amount_" + key;
		var person_loan_amount_id = "person_loan_amount_" +  key;

		var real_amount = $("#" + apply_amount_id).val() - $("#" + rebate_amount_id).val() - $("#" + person_loan_amount_id).val();
		alert(key);
		alert(apply_amount_id);
		alert($("#" + apply_amount_id).val() );
		var l_id = "l_" + key;
		$("#" + l_id).html(real_amount);
	}else{
		alert("非有效数字");
		obj.focus();
	}
	*/
}


function countRebate(){
	var pids = $("#pids").val();
	pids = pids.split(",");
	if($("#is_rebate_deduction").attr("checked")=="checked"){
		var rebate = 0;
		for(var i=0;i<pids.length;i++){
			if(pids[i] != ""){
				var reid = "rebate_deduction_amount_" + pids[i];
				if(!isNaN($(document.getElementById(reid)).val())){
					rebate += Number($(document.getElementById(reid)).val());
				}
			}
		}
		if(rebate == 0){
			rebate = "0.00";
		}else{
			rebate = rebate.toFixed(2);
		}
		$("#ra").html(rebate);
		$("#rebate_amount").val(rebate);
	}else{
		$("#ra").html("0.00");
		$("#rebate_amount").val("0");
		for(var i=0;i<pids.length;i++){
			if(pids[i] != ""){
				var reid = "rebate_deduction_amount_" + pids[i];
				$(document.getElementById(reid)).val("0");
			}
		}
	}
	countpaymentreal();
}

function countPaymentPlan(){
	var pids = $("#pids").val();
	var plan = 0;
	pids = pids.split(",");//payment_amount_11BJ010001-_-!360-_-!
	for(var i=0;i<pids.length;i++){
		if(pids[i] != ""){
			var paid = "payment_amount_" + pids[i];
			if(!isNaN($(document.getElementById(paid)).val())){
				plan += Number($(document.getElementById(paid)).val());
			}
		}
	}
	$("#pal").html(plan);
	$("#payment_amount_plan").val(plan);

	countpaymentreal();
}

function countPersonLoan(){
	var pids = $("#pids").val();
	pids = pids.split(",");
	if($("#is_person_loan_deduction").attr("checked")=="checked"){
		var plaa = 0;
		for(var i=0;i<pids.length;i++){
			if(pids[i] != ""){
				var plid = "person_loan_amount_" + pids[i];
				if(!isNaN($(document.getElementById(plid)).val())){
					plaa += Number($(document.getElementById(plid)).val());
				}
			}
		}
		if(plaa == 0){
			plaa = "0.00";
		}
		$("#plaa").html(plaa);
		$("#person_loan_amount").val(plaa);
	}else{
		$("#plaa").html("0.00");
		$("#person_loan_amount").val("0");
		for(var i=0;i<pids.length;i++){
			if(pids[i] != ""){
				var plid = "person_loan_amount_" + pids[i];
				$(document.getElementById(plid)).val("0");
			}
		}
	}
	countpaymentreal();
}

function doNimPayFirst(obj){
	var id = obj.id;
	alert(id);
	return;
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