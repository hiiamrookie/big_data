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
        	<li class="on"><a>虚拟发票分配执行单</a></li>
        	<li><a href="?o=virtualinvoicesharepayment">虚拟发票分配付款申请</a></li>
      </ul>
    </div>
    <div class="publicform fix">
    <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
    	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
    	<tr>
    		<td style="font-weight:bold;width:120px">搜索相关执行单</td><td colspan="5">
    		执行单号&nbsp;&nbsp;<input type="text" name="search_pid" id="search_pid" style="width:150px;" class="text_new"/>
    		&nbsp;&nbsp;客户名称&nbsp;&nbsp;<input type="text" name="search_cusname" id="search_cusname" style="width:120px;" class="text_new"/>
    		&nbsp;&nbsp;媒体名称&nbsp;&nbsp;<input type="text" name="search_medianame" id="search_medianame" style="width:120px;" class="text_new"/>&nbsp;<input type="button" value="搜索" class="btn" id="searchbtn1" style="cursor:pointer"/>&nbsp;<input type="button" value=" 搜索已付款未到票 " class="longbtn" id="searchbtn2" style="cursor:pointer"/>&nbsp;<input type="button" id="sharepaymentsearch" value="分配付款申请" class="longbtn" style="cursor:pointer"/></td>
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
        <input type="hidden" name="pids" id="pids" value="," /><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="virtual_invoice_share"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
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
	$("#sharepaymentsearch").click(function(){
		location.href= base_url  + "finance/receiveinvoice/?o=virtualinvoicesharepayment";
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
                {field:'c',width:200,title:"OA中供应商名称"},
                {field:'cc',width:200,title:"发票中供应商名称"},
                {field:'d',width:200,align:'right',title:"执行成本"},
                {field:'e',width:200,align:'right',title:"剩余未付款合计"},
                {field:'f',width:200,align:'right',title:"银行已经付款合计"},
                //{field:'g',width:200,align:'right',title:"返点抵扣数合计"},

                {field:'g1',width:200,align:'right',title:"待分配返点"},
                {field:'g2',width:200,align:'right',title:"已开票返点"},
                {field:'g3',width:200,align:'right',title:"无需开票返点"},
                
                {field:'h',width:200,title:"实际付款日期"},
				//{field:'i',width:200,align:'right',title:"虚拟返点发票合计"},
				 {field:'j',width:200,align:'right',title:"成本"},
				{field:'k',width:200,align:'right',title:"进项"},
				{field:'l',width:200,align:'right',title:"价税合计"},
				{field:'m',width:200,align:'right',title:"收到真实发票数合计"},
				//{field:'n',width:200,align:'right',title:"收到真实发票月份"},
				//{field:'o',width:200,align:'right',title:"发票类型"},
				//{field:'p',width:200,align:'right',title:"发票号"},
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

				var nowpids = $("#pids").val();
				$("#pids").val(nowpids.replace("," + rows[i].x + ",",","));
			}
		}
	});
	
	$("#searchbtn1").click(function(){
		 dosearch(1,1);
	});

	$("#searchbtn2").click(function(){
		 dosearch(2,1);
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

function dosearch(type,page){
	if($.trim($("#search_pid").val())=="" && $.trim($("#search_cusname").val())=="" && $.trim($("#search_medianame").val())==""){
		alert("请至少输入一个搜索条件");
	}else{
		
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_executive&type=" + type + "&page=" + page + "&search=" + $("#search_pid").val() + "&cusname=" + $("#search_cusname").val() + "&medianame=" + $("#search_medianame").val() + "&t=" + Math.random() + "&vcode=" + vcode,
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
			   data: "action=get_receive_invoice_pidinfo_search&type=virtual&pids=" + newadd.join(",") + "&t=" + Math.random() + "&vcode=" + vcode,
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

function countTax(obj,isVirtual){
	if(isNaN(obj.value)){
		obj.value=0;
	}else{
		var objid = obj.id;
		objid = objid.split("_");
		var amount_v_id = "amount_" + objid[1] + "_" + objid[2];
		var tax_v_id = "tax_" + objid[1] + "_" + objid[2];
		var taxrate_v_id = "taxrate_" + objid[1] + "_" + objid[2];
		var tr = isVirtual=="1" ? $("#" + taxrate_v_id).val() : $("#tax_rate").val();
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
