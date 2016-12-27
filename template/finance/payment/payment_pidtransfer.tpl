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
		<div class="crumbs">财务管理 - 修改转移</div>
		<div class="tab">
      		<ul>
        		<!--li><a href="[BASE_URL]finance/payment/?o=pidedit">执行单付款修改</a></li-->
        		<li class="on"><a>执行单付款转移</a></li>
        		<!--li><a href="[BASE_URL]finance/receiveinvoice/?o=invoiceedit">发票修改</a></li>
        		<li><a href="[BASE_URL]finance/receiveinvoice/?o=invoicetransfer">发票转移</a></li-->
        		<li><a href="[BASE_URL]finance/payment/?o=deposit2deposit">媒体保证金转移到保证金</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=deposit2pid">媒体保证金转移到执行单</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td>
      					<input type="radio" name="transfertype" value="1" checked/>&nbsp;转出&nbsp;&nbsp;<input type="radio" name="transfertype" value="2"/>&nbsp;转入&nbsp;&nbsp;
      					媒体合同号&nbsp;<input type="text" name="contrct_number" id="contrct_number" style="height:20px;"/>&nbsp;&nbsp;
      					执行单号&nbsp;<input type="text" name="pid" id="pid" style="height:20px;"/>&nbsp;&nbsp;
      					客户名称&nbsp;<input type="text" name="cusname" id="cusname" style="height:20px;"/>&nbsp;&nbsp;
      					媒体名称&nbsp;<input type="text" name="medianame" id="medianame" style="height:20px;"/>&nbsp;&nbsp;
      					<input type="button" value="&nbsp;搜索&nbsp;" class="btn" id="sbtn"/>
      				</td>
      			</tr>
      		</table>
      		<!-- 所选【转出】数据 -->
      		<p/>
      		<table id="dg1" style="width:98%;"></table>
			<div id="toolbar1" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn1">删除</a>
					</div>
				</div>
				<!-- 所选【转入】数据 -->
				<p/>
				<table id="dg2" style="width:98%;"></table>
				<div id="toolbar2" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn2">删除</a>
					</div>
				</div>
				
				<div class="btn_div">
       				 <input type="hidden" name="receive_pids" id="receive_pids" /><input type="hidden" name="pay_pids" id="pay_pids"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="finance_payment_pid_transfer"/><input type="button" value="提 交" class="btn_sub" id="submitbtn" />
      			</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>

<div id="dd">
	<table id="searchdg"></table>
		<div id="tb" style="padding:5px;">
			<a href="javascript:void(0);" class="easyui-linkbutton" data-options="iconCls:'icon-add'" id="addbtn">添加</a>
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
	//收 删除
	$("#cancelbtn2").click(function(){
		var rows = $('#dg2').datagrid('getSelections');
		if(rows.length == 0){
			alert("请选择至少一条记录");
		}else{
			var receive_pids = $("#receive_pids").val();
			for(var i=0;i<rows.length;i++){
				//dg中删除行
				var index = $('#dg2').datagrid('getRowIndex', rows[i]);
				$('#dg2').datagrid('deleteRow', index);  
				$("#receive_pids").val(receive_pids.replace("," + rows[i].xxx + ",",","));
			}
		}
	});

	//付 删除
	$("#cancelbtn1").click(function(){
		var rows = $('#dg1').datagrid('getSelections');
		if(rows.length == 0){
			alert("请选择至少一条记录");
		}else{
			var pay_pids = $("#pay_pids").val();
			for(var i=0;i<rows.length;i++){
				//dg中删除行
				var index = $('#dg1').datagrid('getRowIndex', rows[i]);
				$('#dg1').datagrid('deleteRow', index);  
				$("#pay_pids").val(pay_pids.replace("," + rows[i].xx + ",",","));
			}
		}
	});
	
	$("#dg1").datagrid({
		title:'所选【转出】数据',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		toolbar:'#toolbar1',
		columns:[[
				{field:'xx',checkbox:true},
                {field:'aa',width:'120',title:"执行单号"},
                {field:'bb',width:'180',title:"客户名称"},
                 {field:'cc',width:'140',title:"媒体名称"},
                {field:'dd',align:'right',width:'120',title:"执行成本"},
                {field:'ee',align:'right',width:'120',title:"已付款金额"},
                {field:'ff',align:'right',width:'120',title:"转移付款金额"},
                {field:'gg',align:'right',width:'180',title:"实际已付款金额"}
		]]
	});

	$("#dg2").datagrid({
		title:'所选【转入】数据',
			autoRowHeight:true,
			striped:true,
			rownumbers:true,
			toolbar:'#toolbar2',
			columns:[[
				{field:'xxx',checkbox:true},
				{field:'aaa',width:'120',title:"执行单号"},
				{field:'bbb',width:'180',title:"客户名称"},
				 {field:'ccc',width:'140',title:"媒体名称"},
				{field:'ddd',align:'right',width:'120',title:"执行成本"},
				{field:'eee',align:'right',width:'120',title:"已付款金额"},
				{field:'fff',align:'right',width:'120',title:"转移付款金额"},
				{field:'ggg',align:'right',width:'180',title:"实际已付款金额"}
			]]
	});

	$('#searchdg').datagrid({
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		toolbar:'#tb',
		//pagination:true,
		columns:[[
			{field:'x',checkbox:true},
			{field:'a',width:'100',title:'执行单号'},
			{field:'b',width:'100',title:'客户名称'},
			{field:'c',width:'100',title:'媒体名称'},
			{field:'d',width:'100',title:'执行成本',align:'right'},
			{field:'e',width:'100',title:'已付款情况',align:'right'},
			{field:'f',width:'100',title:'已付款未到票情况',align:'right'},
			{field:'g',width:'100',title:'已执行未付款',align:'right'},
			{field:'h',width:'100',title:'已到票',align:'right'}
		]]
	});

	$("#addbtn").click(function(){
		var rows = $('#searchdg').datagrid('getSelections');
		if(rows.length==0){
			alert("请至少选择一条记录");
		}else{
			var newadd = new Array();
			var exist_pid = $('input[name="transfertype"]:checked').val() == 2 ?  $("#receive_pids").val() : $("#pay_pids").val();
			for(var i=0;i<rows.length;i++){
				if(exist_pid.indexOf("," + rows[i].x + ",") == -1){
					 var newrow = new Array();
					if($('input[name="transfertype"]:checked').val() == 1){
						 newrow["xx"] = rows[i].x;
						 newrow["aa"] = rows[i].a;
						 newrow["bb"] = rows[i].b;
						 newrow["cc"] = rows[i].c;
						 newrow["dd"] = rows[i].d;
						 newrow["ee"] = rows[i].e;
						 newrow["ff"] = '<input type="text" style="height:20px;" value="-' + rows[i].e + '" name="pay_' + rows[i].x + '" id="pay_' + rows[i].x + '" class="validate[required,max[0],min[-' + rows[i].e + ']]"/>';
						 newrow["gg"] = 0;
					 	$('#dg1').datagrid('appendRow',newrow);
					}else{
						newrow["xxx"] = rows[i].x;
						 newrow["aaa"] = rows[i].a;
						 newrow["bbb"] = rows[i].b;
						 newrow["ccc"] = rows[i].c;
						 newrow["ddd"] = rows[i].d;
						 newrow["eee"] = rows[i].e;
						 newrow["fff"] = '<input type="text" style="height:20px;" value="-' + rows[i].e + '" name="receive_' + rows[i].x + '" id="receive_' + rows[i].x + '" class="validate[required,max[0],min[-' + rows[i].e + ']]"/>';
						 newrow["ggg"] = 0;
						$('#dg2').datagrid('appendRow',newrow);
					}
					newadd.push(rows[i].x);
				}
			}
			if(newadd.length>0){
				if(exist_pid == ""){
					exist_pid = "," + newadd.join(",") + ",";
				}else{
					exist_pid = exist_pid + newadd.join(",") + ",";
				}
				if($('input[name="transfertype"]:checked').val() == 1){
					$("#pay_pids").val(exist_pid);
				}else{
					$("#receive_pids").val(exist_pid);
				}
			}
			
			$("#dd").dialog({
				closed:true
			});
		}
	});

	$('#dd').dialog({
	    title: '转移搜索',
	    width: window.screen.width * 2 / 3,
	    height: window.screen.height / 2,
	    closed: true,
	    cache : false,
	    modal : true
	});


	
	//提交按钮
	$("#submitbtn").click(function(){
		$("#formID").submit();
	});

	/*
	//保存按钮
	$("#save").click(function(){
		//应付金额
		$("#payment_amount_plan").removeClass("validate[required,custom[money]]");
		$("#payment_amount_plan").addClass("validate[optional,custom[money]]");

		//付款时间
		$("#payment_date").removeClass("validate[required]");

		//action
		$("#action").val("payment_person_apply_temp");
		
		$("#formID").submit();
	});
	*/


	
	$("#sbtn").click(function(){
		var transfertype = $('input[name="transfertype"]:checked').val();
		var contrct_number = $.trim($("#contrct_number").val());
		var pid = $.trim($("#pid").val());
		var cusname = $.trim($("#cusname").val());
		var medianame = $.trim($("#medianame").val());
		
		
		if(contrct_number == "" && pid=="" && cusname=="" && medianame==""){
			alert("请至少输入一个搜索条件");
		}else{
			var transfertype = $('input[name="transfertype"]:checked').val();
			var url = base_url + "get_data.php?action=getPaidPidFinanceInfo&transfertype=" + transfertype + "&contrct_number=" + encodeURI(contrct_number) + "&pid=" + encodeURI(pid) + "&cusname=" + encodeURI(cusname) + "&medianame=" + encodeURI(medianame);
			if(transfertype == 1){
				var title = '需要【转出】数据';
			}else{
				var title = '需要【转入】数据';
			}
			$('#searchdg').datagrid({
				 title:title,
				 url:url
			});
			
			$("#dd").dialog({
			    closed: false
			});
		}
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

function dosearch(page){
	var transfertype = $('input[name="transfertype"]:checked').val();
	var contrct_number = $.trim($("#contrct_number").val());
	var pid = $.trim($("#pid").val());
	var cusname = $.trim($("#cusname").val());
	var medianame = $.trim($("#medianame").val());
	
	
	if(pid=="" && cusname=="" && medianame==""){
		alert("请至少输入一个搜索条件");
	}else{
		
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_executive&page=" + page + "&search=" + $("#search_executive").val() + "&cusname=" + $("#cusname").val() + "&projectname=" + $("#projectname").val() + "&medianame=" + $("#medianame").val() + "&t=" + Math.random() + "&vcode=" + vcode,
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

function removepid(v,pid){
	$(v).parent().parent().remove();
	var nowpids = $("#pids").val();
	$("#pids").val(nowpids.replace("," + pid + ",",","));
}

</script>
</body>
</html>
