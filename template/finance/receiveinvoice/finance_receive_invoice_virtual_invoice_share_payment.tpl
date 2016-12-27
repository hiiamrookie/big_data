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
    <div class="crumbs">财务管理</div>
    <div class="tab">
      <ul>
      		<li><a href="?o=receiveinvoicelist">收票对账单信息列表</a></li>
			<li><a href="?o=receiveinvoiceadd">新建收票对账单信息</a></li>
        	<li><a href="?o=receiveinvoiceimport">收票对账单信息导入</a></li>
        	<li><a href="?o=pidsharelist">已分配执行单记录</a></li>
        	<li><a href="?o=paymentsharelist">已分配付款申请记录</a></li>
        	<li><a href="?o=virtualinvoiceshare">虚拟发票分配执行单</a></li>
        	<li class="on"><a>虚拟发票分配付款申请</a></li>
      </ul>
    </div>
    <div class="publicform fix">
    <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
    	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
    	<tr>
    		<td style="font-weight:bold;width:120px">搜索相关付款申请</td><td colspan="5">
    		媒体名称&nbsp;&nbsp;<input type="text" name="search_medianame" id="search_medianame" style="width:120px;" class="text_new"/>
    		&nbsp;&nbsp;付款时间&nbsp;&nbsp;<input type="text" name="search_paymentdate" id="search_paymentdate" style="width:120px;" onclick="WdatePicker()" class="text_new Wdate" readonly="readonly"/>
    		&nbsp;&nbsp;实付款金额&nbsp;&nbsp;<input type="text" name="search_payment_real" id="search_payment_real" style="width:120px;" class="text_new"/>&nbsp;<input type="button" value="搜索" class="btn" id="searchbtn1"/>&nbsp;<input type="button" id="sharepid" value=" 分配执行单 " class="longbtn" style="cursor:pointer"/></td>
    	</tr>
    	<tr><td id="search_result" colspan="6"></td></tr>
    	</table>
    	<p/>
    	<table id="dg"></table>
		<div id="toolbar" style="padding:5px;height:auto">
			<div style="margin-bottom:5px">
				<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn">删除</a>
			</div>
		</div>
      <div class="btn_div">
       <input type="hidden" name="itemids" id="itemids" value=","/><input type="hidden" name="pids" id="pids" value="," /><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="virtual_invoice_payment_share"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
      </div>
      </form>
      <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    </div>
  </div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script>
var vcode = '[VCODE]';
var base_url = '[BASE_URL]';
$(document).ready(function(){
	$("#sharepid").click(function(){
		location.href= base_url  + "finance/receiveinvoice/?o=virtualinvoiceshare";
	});
	
	$("#dg").datagrid({
		title:'所选数据',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		toolbar:'#toolbar',
		columns:[[
				{field:'x',checkbox:true},
                {field:'a',width:100,title:"执行单号"},
                {field:'b',width:200,title:"客户名称"},
                 {field:'c',width:200,title:"供应商"},
                {field:'d',width:200,align:'right',title:"执行成本"},
                {field:'e',width:200,align:'right',title:"剩余未付款合计"},
                {field:'f',width:200,align:'right',title:"银行已经付款合计"},
                {field:'g',width:200,align:'right',title:"返点抵扣数合计"},
                {field:'aa',width:200,align:'right',title:"员工借款合计"},
                {field:'bb',width:200,align:'right',title:"收付对冲合计"},
                {field:'h',width:200,title:"实际付款日期"},
				{field:'i',width:200,align:'right',title:"虚拟返点发票合计"},
				 {field:'j',width:200,align:'right',title:"成本"},
				{field:'k',width:200,align:'right',title:"进项"},
				{field:'l',width:200,align:'right',title:"价税合计"},
				{field:'cc',width:200,align:'right',title:"真实返点发票合计"},
				{field:'m',width:200,align:'right',title:"收到真实发票数合计"},
				//{field:'n',width:200,align:'right',title:"收到真实发票月份"},
				//{field:'o',width:200,align:'right',title:"发票类型"},
				{field:'p',width:200,align:'right',title:"发票号"},
				{field:'q',width:200,align:'right',title:"税率"},
				{field:'s',width:200,align:'right',title:"已付款未到票"}
		]]
	});

	
	$("#cancelbtn").click(function(){
		var rows = $('#dg').datagrid('getSelections');
		if(rows.length >0){
			for(var i = 0;i<rows.length;i++){
				//dg中删除行
				var index = $('#dg').datagrid('getRowIndex', rows[i]);
				$('#dg').datagrid('deleteRow', index);  

				//var nowpids = $("#pids").val();
				//$("#pids").val(nowpids.replace("," + rows[i].x + ",",","));
				var itemids = $("#itemids").val();
				$("#itemids").val(itemids.replace("," + rows[i].x + ",",","));
			}

			var itemids = $("#itemids").val();
			var nowpids = $("#pids").val();
			var tnowpids = nowpids.split(",");
			for(var xx=0;xx<tnowpids.length;xx++){
				if(tnowpids[xx] !=""){
					if(itemids.indexOf("_" + tnowpids[xx]) == -1){
						$("#pids").val(nowpids.replace("," + tnowpids[xx] + ",",","));
					}
				}
			}
		}else {
			alert("请至少选择一条记录删除");
		}
	});
	
	$("#searchbtn1").click(function(){
		 dosearch(1);
	});
	
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	})	
});

function dosearch(page){
	if($.trim($("#search_medianame").val())=="" && $.trim($("#search_paymentdate").val())==""  && $.trim($("#search_payment_real").val())==""){
		alert("请至少输入一个搜索条件");
	}else{
		
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_payment_apply&page=" + page + "&search_medianame=" + $("#search_medianame").val() + "&search_paymentdate=" + $("#search_paymentdate").val() +  "&search_payment_real=" + $("#search_payment_real").val() + "&t=" + Math.random() + "&vcode=" + vcode,
			dataType:'text',
			async: false,
			success: function(msg){
				$("#search_result").empty();
				$("#search_result").append(msg);
			},
		 	error: function(e){
		 		alert("搜索付款申请记录异常");
		 	}
		});

	}
}

function pidmove(){
	var sel = false;
	var newadd = new Array();
	var itemadd = new Array();
	$('input[name="paymentselect"]:checked').each(function(){
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
			   data: "action=get_receive_invoice_payment_search&type=virtual&pids=" + newadd.join(",") + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   rows = $.parseJSON(msg);
				   for (var i=0;i<rows.rows.length;i++){
					   //alert();
					   var itemids = $("#itemids").val();
					   if(itemids.indexOf("," + rows.rows[i].x + ",") == -1){
						   $("#itemids").val(itemids + rows.rows[i].x + ",");
						}
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

function countTax(obj,isVirtual){
	if(isNaN(obj.value)){
		obj.value=0;
	}else{
		var objid = obj.id;
		//alert(objid);
		objid = objid.split("_");
		var amount_v_id = "amount_" + objid[1] + "_" + objid[2]+ "_" + objid[3];
		var tax_v_id = "tax_" + objid[1] + "_" + objid[2]+ "_" + objid[3];
		var taxrate_v_id = "taxrate_" + objid[1] + "_" + objid[2]+ "_" + objid[3];
		//alert(taxrate_v_id);
		var tr = isVirtual=="1" ? $("#" + taxrate_v_id).val() : $("#tax_rate").val();
		//var tr = $("#tax_rate").val();
		var amount_v = Number(obj.value / (1 + tr / 100));
		amount_v = amount_v.toFixed(2);
		var tax_v = Number(obj.value - amount_v);
		tax_v = tax_v.toFixed(2);
		
		$("#" + amount_v_id).val(amount_v);
		$("#" + tax_v_id).val(tax_v);
	}
	
}
</script>
</body>
</html>
