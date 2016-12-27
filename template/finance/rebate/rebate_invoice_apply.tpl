<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
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
		<div class="crumbs">财务管理 - 返点开票申请</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>申请返点开票</a></li>
        		<li><a href="[BASE_URL]finance/rebate/?o=apply_mylist">已申请返点开票列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td style="font-weight:bold;width:150px">媒体</td>
          			<td><input type="text" name="media_name" id="media_name" class="validate[required,maxSize[200]]" style="width:300px;height:20px;"/></td>
      			</tr>
      			<tr>
      				<td style="font-weight:bold;width:150px">媒体支付方式</td>
          			<td>
						<input type="radio" name="media_payment_type" value="1" checked="checked" /> 付现 &nbsp;
            			<input type="radio" name="media_payment_type" value="2" /> 抵应付账款
					</td>
      			</tr>
      			<tr>
      				<td style="font-weight:bold;width:150px">媒体返点比例</td>
          			<td><input type="text" name="media_rebate_rate" id="media_rebate_rate" style="height:20px;" class="validate[required,min[0],max[100]]" />&nbsp;%</td>
      			</tr>
      			<tr>
      				<td style="font-weight:bold;width:150px">关联方式</td>
          			<td>
          				<input type="radio" name="relation_type" value="1" checked/>&nbsp;关联执行单&nbsp;&nbsp;
          				<input type="radio" name="relation_type" value="2"/>&nbsp;关联客户&nbsp;&nbsp;
          				<input type="radio" name="relation_type" value="3"/>&nbsp;关联付款申请&nbsp;&nbsp;
          				<span id="media_payment_type_select"><input type="radio" name="relation_type" value="4"/>&nbsp;关联媒体+执行时间段&nbsp;&nbsp;
          				<!--<input type="radio" name="relation_type" value="5"/>&nbsp;--></span>
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
          			<td style="font-weight:bold;width:150px">开票总金额</td>
          			<td><font color="#ff9933"><b><span id="invoceamount" style="font-size:15px">0.00</span> 元</b></font></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;">开票类型</td>
          			<td>
          				<input type="radio" value="1" name="invoice_type" checked="checked" onclick="showzp(1)"/> 普票 &nbsp;
            			<input type="radio" value="2" name="invoice_type" onclick="showzp(2)" /> 增票 &nbsp; 
            			<div id="showzp" style="display:none">
            				<br />
            				<div>纳税人识别号：&nbsp;<input type="text" style="width:350px;height:20px;" id="d1" name="d1"/></div>
                			<div>地址、电话：&nbsp;&nbsp;&nbsp;<input type="text" style="width:350px;height:20px;" id="d2" name="d2"/></div>
                			<div>开户行及账号：&nbsp;<input type="text" style="width:350px;height:20px;" id="d3" name="d3"/></div>
            			</div>
          			</td>
       	 		</tr>
        		<tr>
          			<td style="font-weight:bold">开票抬头</td>
          			<td><input type="text" class="validate[required,maxSize[200]]" style="width:300px;height:20px;" id="title" name="title"/></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold">开票内容</td>
          			<td><input type="text" class="validate[required,maxSize[200]]" style="width:300px;height:20px;" id="content" name="content"/></td>
        		</tr>
		        <tr>
		          <td style="font-weight:bold">备注</td>
		          <td><textarea name="remark" id="remark" class="validate[optional,maxSize[500]] textarea" rows="3" style="width:300px;height:84px"></textarea></td>
		        </tr>
      		</table>
      		<div class="btn_div">
        		<input type="hidden" name="selectitem" id="selectitem"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="rebate_invoice_apply"/><input type="hidden" name="pids" id="pids" value=","/><input type="submit" value="提 交" class="btn_sub" id="submit" />
      		</div>
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
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
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
			
			/*
			var newadd = new Array();
			var deposit_deductiuon = $("#deposit_deductiuon").val();
			for(var i=0;i<rows.length;i++){
				if(deposit_deductiuon.indexOf("," + rows[i].ck + ",") == -1){
				
					 var newrow = new Array();
					 newrow["dck"] = rows[i].ck;
					 newrow["dcid"] = rows[i].cid;
					 newrow["dcusname"] = rows[i].cusname;
					 newrow["dmedia"] = rows[i].media;
					 newrow["dgd_amount"] = rows[i].gd_amount;
					 newrow["ddeduction"] = '<input type="text" style="height:20px;" value="-' + rows[i].gd_amount + '" name="ddeduction_' + rows[i].ck + '" id="ddeduction_' + rows[i].ck + '" class="validate[required,max[0],min[-' + rows[i].gd_amount + ']]" onblur="javascript:depositonblur(this);"/>';
					 $('#selectdeposit').datagrid('appendRow',newrow);
				
						newadd.push(rows[i].ck);
				}
			}
			if(deposit_deductiuon == ""){
				deposit_deductiuon = "," + newadd.join(",") + ",";
			}else{
				deposit_deductiuon = deposit_deductiuon + newadd.join(",") + ",";
			}
			$("#deposit_deductiuon").val(deposit_deductiuon);

			$("#dd").dialog({
				closed:true
			});

			countpaymentreal();
			*/
		}
	});
	
	$('#dd').dialog({
	    title: '搜索结果',
	    width: window.screen.width * 2 / 3,
	    height: window.screen.height / 2,
	    closed: true,
	    cache : false,
	    modal : true
	});
	
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
			countInvoiceAmount();
		}
	});
	
	$('input[name="media_payment_type"]').click(function(){
		if($(this).val()=="2"){
			$("#media_payment_type_select").hide();
			if($('input[name="relation_type"]:checked').val()=="4" || $('input[name="relation_type"]:checked').val()=="5" ){
				$('input[name="relation_type"][value="1"]').prop("checked",true);
			}
		}else{
			$("#media_payment_type_select").show();
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



	
	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true)	$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
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

function doadd(){
	var billtype = $('input:radio[name="billtype"]:checked').val();
	var nowpids = $("#pids").val();
	if(nowpids.indexOf("," + $("#search").val() + ",") == -1){
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_invoice_executive&billtype=" + billtype + "&search=" + $("#search").val() + "&t=" + Math.random() + "&vcode=" + vcode,
			dataType:'text',
			async: false,
			success: function(msg){
				if(msg == "0"){
					alert("没有找到该执行单");
				}else if(msg == "1"){
					if(billtype == "1"){
						alert("该执行单的发票类型不是广告业，请重新选择");
					}else if(billtype == "2"){
						alert("该执行单的发票类型不是服务业，请重新选择");
					}
				}else{
					$("#pidinfo").append(msg);
					$("#pids").val(nowpids + $("#search").val() + ",");
					getallamount();
					$('input:radio[name="billtype"]').each(function(){
						$(this).attr("disabled",true);
					});
				}
			},
		 	error: function(e){
		 		alert("搜索关联执行单记录异常");
		 	}
		});
	}
}

function removepid(v,pid){
	$(v).parent().remove();
	var nowpids = $("#pids").val();
	$("#pids").val(nowpids.replace("," + pid + ",",","));
	getallamount();
	if($("#pids").val() == ","){
		$('input:radio[name="billtype"]').each(function(){
			$(this).attr("disabled",false);
		});
	}
}

function getallamount(){
	var amount=0;
	var t=0;
	var nowpids = $("#pids").val();
	nowpids = nowpids.split(",");
	for(var i=0;i<nowpids.length;i++){
		if(nowpids[i] != ""){
			var id = "#amount_" + nowpids[i];
			amount+=Number($(id).val());
		}
	}
	//$("#pidinfo").children().each(function(index, element) {
	//	amount+=Number($(this).find("#amount").val());
	//});
	$("#invoceamount").html(amount.toFixed(2));
}

function check_amount(obj,id){
	var newamount = Number(obj.value);
	var aid = "oldamount_" + id;
	var olamount = Number($("#" + aid).val()); 
	var spanid = "span_" + id;
	if(olamount >= newamount){
		$("#" + spanid).html("");
	}else{
		$("#" + spanid).html("<font color=\"red\">请注意：开票金额高于执行单金额</font>");
	}
}
function showzp(n){
	if (n == 2) {
		$("#showzp").show();
		$("#d1").addClass("validate[required,maxSize[200]]");
		$("#d2").addClass("validate[required,maxSize[200]]");
		$("#d3").addClass("validate[required,maxSize[200]]");
	}else{
		$("#showzp").hide();
		$("#d1").removeClass("validate[required,maxSize[200]]");
		$("#d2").removeClass("validate[required,maxSize[200]]");
		$("#d3").removeClass("validate[required,maxSize[200]]");
	}
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

function countInvoiceAmount(){
	var pids = $("#pids").val();
	pids = pids.split(",");
	var sumamount = 0;
	for(var i=0;i<pids.length;i++){
		if(pids[i]!=""){
			var id = "amount_" + pids[i];
			var amount = $("#" + id).val();
			if(isNaN(amount)){
				$("#" + id).val("0");
				amount = 0;
			}
			sumamount += Number(amount);
		}
	}
	$("#invoceamount").html(sumamount);
}
</script>
</body>
</html>
