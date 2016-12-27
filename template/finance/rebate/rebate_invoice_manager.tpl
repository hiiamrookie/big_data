<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/default/easyui.css"/>
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/icon.css"/>
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 返点开票管理</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>返点开票管理</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=apply_manager">返点开票申请管理</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=invoice_nocollection">已开票未回款查询</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=query">返点查询</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=rebate_transfer_list">返点转移申请列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td>
      					媒体名称&nbsp;<input type="text" name="medianame" id="medianame" style="height:20px;"/>&nbsp;&nbsp;
      					申请付款时间&nbsp;<input type="text" name="payment_date" id="payment_date" class="Wdate" onclick="WdatePicker()"/>&nbsp;&nbsp;
      					<input type="button" value=" 搜索 " class="btn" id="sbtn"/>&nbsp;&nbsp;
      					<input type="button" value=" 搜索待开票返点 " class="longbtn" id="needinvoicesbtn"/>
      				</td>
      			</tr>
      		</table>
      		<p/>
      		<table id="dg" style="width:100%"></table>
      		<p></p>
      		<table id="rebatedg" style="width:100%"></table>
      		<p></p>
      		<div class="btn_div">
        		<input type="hidden" name="isthesame" id="isthesame" value="1"/><input type="hidden" name="selectitem" id="selectitem"/><input type="hidden" name="pids" id="pids" value=","/><input type="hidden" name="itemids" id="itemids"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="rebate_recover_need_invoice"/><input type="button" value="还原成待开票" class="btn_sub" id="recoverbtn" name="savebtn"/><input type="button" value="无需开票" class="btn_sub" id="noinvoicebtn" name="subbtn"/>
      		</div>
      		<p></p>
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1" id="other">
      			<tr>
      				<td style="font-weight:bold;width:150px">关联方式</td>
          			<td>
          				<input type="radio" name="relation_type" value="1" checked/>&nbsp;关联执行单&nbsp;&nbsp;
          				<input type="radio" name="relation_type" value="2"/>&nbsp;关联客户&nbsp;&nbsp;
          				<input type="radio" name="relation_type" value="3"/>&nbsp;关联付款申请&nbsp;&nbsp;
          				<span id="media_payment_type_select"><input type="radio" name="relation_type" value="4"/>&nbsp;关联媒体+执行时间段&nbsp;&nbsp;
          				<!--<input type="radio" name="relation_type" value="5"/>&nbsp;上传文件--></span>
					</td>
      			</tr>
      			<tr>
      				<td style="width:150px"></td>
          			<td id="searchshow">执行单号&nbsp;<input type="text" style="height:20px;" name="s1" id="s1"/>&nbsp;&nbsp;<input type="button" value="搜索" class="btn" id="search1" onclick="javascript:searchr(1);"/>
					</td>
      			</tr>
      			<tr>
          			<td colspan="2">
          				<table id="pdg" width="100%"></table>
          				<div id="toolbar1" style="padding:5px;height:auto">
							<div style="margin-bottom:5px">
								<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn1">删除</a>
							</div>
						</div>
					</td>
      			</tr>
      			<tr>
      				<td colspan="2"><div class="btn_div"><input type="submit" value="提交" class="btn_sub" name="submitbtn" id="submitbtn"/></div></td>
      			</tr>
      		</table>
      		
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<div id="dd">
	<table id="searchdg"></table>
		<div id="tb" style="padding:5px;height:auto">
			<a href="javascript:void(0);" class="easyui-linkbutton" data-options="iconCls:'icon-add'" id="addbtn">添加</a>
		</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script src="[BASE_URL]script/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {	
	$('#dd').dialog({
	    title: '搜索结果',
	    width: window.screen.width * 2 / 3,
	    height: window.screen.height / 2,
	    closed: true,
	    cache : false,
	    modal : true
	});

	$("#addbtn").click(function(){
		var rows = $('#searchdg').datagrid('getSelections');
		if(rows.length==0){
			alert("请至少选择一条记录");
		}else{
			var selectitem = $("#selectitem").val();
			var newadd = new Array();
			for(var i=0;i<rows.length;i++){
				if(selectitem.indexOf("," + rows[i].xx + "^" + rows[i].type + ",")==-1){
					newadd.push(rows[i].xx + "^" + rows[i].type);
				}
			}

			if(selectitem == ""){
				selectitem = "," + newadd.join(",") + ",";
			}else{
				selectitem = selectitem + newadd.join(",") + ",";
			}
			$("#selectitem").val(selectitem);
			
			

			if(newadd.length > 0){
				//newadd值去查找相关数据
				$.ajax({
					   type: "post",
					   url: base_url + "get.php",
					   cache:"false",
					   data: "action=get_reabte_invoice_pidinfo_search&selectitem=" + newadd.join(",") + "&t=" + Math.random() + "&vcode=" + vcode,
					   dataType:'text',
					   async: false,
					   success: function(msg){
						  	rows = $.parseJSON(msg);
						  	if(rows.total == "0"){
								alert("没有符合条件的记录，请重新选择");
							}else{
								var getpids = rows.pids;
							  	for (var i=0;i<rows.rows.length;i++){
							  		$('#pdg').datagrid('appendRow',rows.rows[i]);
								}

							  	var pids = $("#pids").val();
							  	if(pids == ""){
							  		pids = "," + getpids + ",";
								}else{
									pids = pids + getpids + ",";
								}
								$("#pids").val(pids);
							}
					   },
				 	   error: function(e){
				 		   alert("查找相关数据异常");
				 	   }
				});
			}

			$("#dd").dialog({
				closed:true
			});
		}
	});
	
	$("#dg").datagrid({
		title:'付款申请列表',
		autoRowHeight:true,
		rownumbers:true,
		striped:true,
		pagination:true,
		singleSelect:true,
		pageList:[10,30,50],
		columns:[[
			{field:'a',width:'200',title:"媒体名称"},
			{field:'b',width:'200',align:'center',title:"约定付款时间"},
			{field:'c',width:'200',align:'right',title:"实际付款金额"},
			{field:'d',width:'200',align:'right',title:"返点抵扣金额"},
			{field:'e',width:'100',align:'center',title:""}
		]]
	});

	$("#rebatedg").datagrid({
		title:'返点列表',
		autoRowHeight:true,
		striped:true,
		columns:[[
		    {field:'xx',checkbox:true},
			{field:'aa',width:'200',title:"执行单号"},
			{field:'bb',width:'200',title:"客户名称"},
			{field:'cc',width:'200',title:"项目名称"},
			{field:'dd',width:'200',title:"OA供应商"},
			{field:'ee',width:'200',title:"发票媒体名称"},
			{field:'ff',width:'200',align:'right',title:"媒体执行成本"},
			{field:'gg',width:'200',align:'right',title:"已执行未付成本金额"},
			{field:'hh',width:'200',align:'right',title:"已付成本合计金额"},
			{field:'ii',width:'200',align:'right',title:"待开票返点"},
			{field:'jj',width:'200',align:'right',title:"已开票返点"},
			{field:'kk',width:'200',align:'right',title:"无需开票返点"},
			{field:'ll',width:'200',align:'right',title:"虚拟发票合计金额"},
			{field:'mm',width:'200',align:'right',title:"真实发票到票合计金额"},
			{field:'nn',width:'200',align:'right',title:"已付款未到票金额"},
			{field:'oo',width:'200',align:'left',title:"返点金额"}
		]]
	});

	

	$("#sbtn").click(function(){
		 dosearch(1);
	});

	$("#needinvoicesbtn").click(function(){
		dosearch(2);
	});

	//还原成待开票
	$("#recoverbtn").click(function(){
		var rows = $('#rebatedg').datagrid('getSelections');
		if(rows.length == 0){
			alert("请选择至少一条记录");
		}else{
			$("#action").val("rebate_recover_need_invoice");
			var add_array = new Array();
			for(var i=0;i<rows.length;i++){
				add_array.push(rows[i].xx);
			}
			$("#itemids").val(add_array.join(","));
			$("#formID").submit();
		}
	});

	//无需开票
	$("#noinvoicebtn").click(function(){
		var rows = $('#rebatedg').datagrid('getSelections');
		if(rows.length == 0){
			alert("请选择至少一条记录");
		}else{
			$("#action").val("rebate_no_need_invoice");
			var add_array = new Array();
			for(var i=0;i<rows.length;i++){
				add_array.push(rows[i].xx);
			}
			$("#itemids").val(add_array.join(","));
			if(window.confirm("返点分配与执行单是否一致?")){
				$("#isthesame").val("1");
				$("#formID").submit();
			}else{
				$("#other").show();
				$("#isthesame").val("0");
				$("#pdg").datagrid({
					title:'返点开票列表',
					autoRowHeight:true,
					striped:true,
					rownumbers:true,
					singleSelect:false,
					toolbar:'#toolbar1',
					columns:[[
							{field:'x',checkbox:true},
			                {field:'a',width:'120',title:"执行单号"},
			                {field:'b',width:'180',title:"客户名称"},
			                {field:'c',width:'140',title:"项目名称"},
			                {field:'d',width:'140',title:"供应商"},
			                {field:'e',align:'right',width:'120',title:"媒体执行成本"},
			                {field:'f',align:'right',width:'120',title:"媒体已付款金额"},
			                {field:'g',align:'right',width:'120',title:"媒体已到票金额"},
			                {field:'h',align:'right',width:'120',title:"已开返点发票金额"},
			                {field:'i',width:'200',title:"本次开返点发票金额"}
					]]
				});
				
				$("#cancelbtn1").click(function(){
					var rows = $('#pdg').datagrid('getSelections');
					if(rows.length == 0){
						alert("请选择至少一条记录");
					}else{
						var pids = $("#pids").val();
						for(var i=0;i<rows.length;i++){
							//dg中删除行
							var index = $('#pdg').datagrid('getRowIndex', rows[i]);
							$('#pdg').datagrid('deleteRow', index);  
							$("#pids").val(pids.replace("," + rows[i].x + ",",","));
						}
					}
				});

				$('input[name="relation_type"]').click(function(){
					var x = "";
					if($(this).val()=="1"){
						x = '执行单号&nbsp;<input type="text" style="height:20px;" name="s1" id="s1"/>&nbsp;&nbsp;<input type="button" value="搜索" class="btn" id="search1" onclick="javascript:searchr(1);"/>';
					}else if($(this).val()=="2"){
						x = '客户名称&nbsp;<input type="text" style="height:20px;" name="s1" id="s1"/>&nbsp;&nbsp;<input type="button" value="搜索" class="btn" id="search1" onclick="javascript:searchr(2);"/>';
					}else if($(this).val()=="3"){
						x = '媒体名称&nbsp;<input type="text" style="height:20px;" name="s1" id="s1"/>&nbsp;&nbsp;<input type="button" value="搜索" class="btn" id="search1" onclick="javascript:searchr(3);"/>';
					}else if($(this).val()=="4"){
						x = '媒体名称&nbsp;<input type="text" style="height:20px;" name="s1" id="s1"/>&nbsp;&nbsp;执行开始时间&nbsp;<input type="text" onclick="WdatePicker()" style="width:100px" name="s2" id="s2" readonly class="Wdate">&nbsp;&nbsp;执行结束时间&nbsp;<input type="text" onclick="WdatePicker()" style="width:100px" name="s3" id="s3" readonly class="Wdate">&nbsp;&nbsp;<input type="button" value="搜索" class="btn" id="search1" onclick="javascript:searchr(4);"/>';
					}else if($(this).val()=="5"){
						x = '选择文件&nbsp;<input type="file" name="s1" id="s1"/>&nbsp;&nbsp;返点开始时间&nbsp;<input type="text" onclick="WdatePicker()" style="width:100px" name="s2" id="s2" readonly class="Wdate">&nbsp;&nbsp;返点结束时间&nbsp;<input type="text" onclick="WdatePicker()" style="width:100px" name="s3" id="s3" readonly class="Wdate">';
					}

					$("#searchshow").html(x);
				});
			}
		}
	});

	$("#other").hide();
});

function dosearch(type){
	var medianame = $.trim($("#medianame").val());
	var payment_date = $.trim($("#payment_date").val());
	if(medianame=="" && payment_date==""){
		alert("请至少输入一个搜索条件");
	}else{
		var url = base_url + "get_data.php?action=getPaymentApply&type=" + encodeURI(type) + "&medianame=" + encodeURI(medianame) + "&payment_date=" +  encodeURI(payment_date);
		$("#dg").datagrid({
			url:url
		});
	}
}

function showDebate(apply_id){
	$("#rebatedg").datagrid({
		url:base_url + "get_data.php?action=getPaymentRebateItems&apply_id=" + apply_id,
		onLoadSuccess:function(){
            $('#rebatedg').datagrid('selectAll');
        }
	});
}

function searchr(type){
	var data = "";
	var s1 = "";
	var s2 = "";
	var s3 = "";
	var validate = false;
	var columns = [[]];
	if(type=="1"){
		s1 = $.trim($("#s1").val());
		if(s1 != ""){
			validate = true;
			data = "pid=" + s1;
			columns = [[
				{field:'xx',checkbox:true},
				{field:'aa',width:'120',title:"执行单号"},
				{field:'bb',width:'200',title:"项目名称"}
			]];
		}
	}else if(type=="2"){
		s1 = $.trim($("#s1").val());
		if(s1 != ""){
			validate = true;
			data = "cusname=" + s1;
			columns = [[
				{field:'xx',checkbox:true},
				{field:'aa',width:'200',title:"客户名称"},
				{field:'bb',width:'200',title:"合同号"},
				{field:'cc',width:'200',title:"合同名称"}
			]];
		}
	}else if(type=="3"){
		s1 = $.trim($("#s1").val());
		if(s1 != ""){
			validate = true;
			data = "medianame=" + s1;
			columns = [[
				{field:'xx',checkbox:true},
				{field:'aa',width:'200',title:"媒体名称"},
				{field:'bb',width:'200',align:'center',title:"约定付款时间"},
				{field:'cc',width:'200',align:'right',title:"应付金额"},
				{field:'dd',width:'200',align:'right',title:"实付金额"}
			]];
		}
	}else if(type=="4"){
		s1 = $.trim($("#s1").val());
		s2 = $.trim($("#s2").val());
		s3 = $.trim($("#s3").val());
		if(s1 !="" || s2 !="" || s3 !=""){
			validate = true;
			data = "medianame=" + s1 + "&starttime=" + s2 + "&endtime=" + s3;
			columns = [[
				{field:'xx',checkbox:true},
				{field:'aa',width:'120',title:"执行单号"},
				{field:'bb',width:'200',title:"项目名称"},
				{field:'cc',width:'200',align:'center',title:"项目开始时间"},
				{field:'dd',width:'200',align:'center',title:"项目结束时间"}
			]];
		}
	}

	if(!validate){
		alert("请至少输入一个搜索条件");
	}else{
		$("#searchdg").datagrid({
			autoRowHeight:true,
			striped:true,
			rownumbers:true,
			singleSelect:false,
			toolbar:'#tb',
			url:base_url + "get_data.php?action=searchRebateInvoiceApplyPid&type=" + type + "&" + data,
			pagination:true,
			columns:columns
		});
		
		$('#dd').dialog({
		    closed: false
		});
	}
}
</script>
</body>
</html>
