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
		<div class="crumbs">财务管理 - 修改转移</div>
		<div class="tab">
      		<ul>
        		<!--li><a href="[BASE_URL]finance/payment/?o=pidedit">执行单付款修改</a></li-->
        		<li><a href="[BASE_URL]finance/payment/?o=pidtransfer">执行单付款转移</a></li>
        		<li><a href="[BASE_URL]finance/receiveinvoice/?o=invoiceedit">发票修改</a></li>
        		<li class="on"><a>发票转移</a></li>
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
      					媒体合同号&nbsp;<input type="text" name="cusname_number" id="cusname_number" style="height:20px;"/>&nbsp;&nbsp;
      					执行单号&nbsp;<input type="text" name="pid" id="pid" style="height:20px;"/>&nbsp;&nbsp;
      					客户名称&nbsp;<input type="text" name="cusname" id="cusname" style="height:20px;"/>&nbsp;&nbsp;
      					媒体名称&nbsp;<input type="text" name="medianame" id="medianame" style="height:20px;"/>&nbsp;&nbsp;
      					<input type="button" value="&nbsp;搜索&nbsp;" class="btn"/>
      				</td>
      			</tr>
      		</table>
      		
      		<!-- 所选【转出】数据 -->
      		<p/>
      		<table class="easyui-datagrid" id="dg1" style="width:98%;" data-options="
      			title:'所选【转出】数据',
      			autoRowHeight:true,
      			striped:true,
      			rownumbers:true,
      			toolbar:'#toolbar1'">
      			<thead>
					<tr>
						<th data-options="field:'xx',checkbox:true"></th>
		                <th data-options="field:'aa',width:'120'">执行单号</th>
		                <th data-options="field:'bb',width:'180'">客户名称</th>
		                 <th data-options="field:'cc',width:'140'">媒体名称</th>
		                <th data-options="field:'dd',align:'right',width:'120'">执行成本</th>
		                <th data-options="field:'ee',align:'right',width:'120'">已付款金额</th>
		                <th data-options="field:'ff',align:'right',width:'120'">已付款未到票</th>
		                <th data-options="field:'gg',align:'right',width:'180'">上次收票金额</th>
		                <th data-options="field:'hh',align:'right',width:'180'">本次转出票金额</th>
		                <th data-options="field:'ii',align:'right',width:'180'">本次实际收票金额</th>
					</tr>
       			 </thead>		   
				</table>
				<div id="toolbar1" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn1">删除</a>
					</div>
				</div>
				<!-- 所选【转入】数据 -->
				<p/>
      		<table class="easyui-datagrid" id="dg2" style="width:98%;" data-options="
      			title:'所选【转入】数据',
      			autoRowHeight:true,
      			striped:true,
      			rownumbers:true,
      			toolbar:'#toolbar2'">
      			<thead>
					<tr>
						<th data-options="field:'xxx',checkbox:true"></th>
		                <th data-options="field:'aaa',width:'120'">执行单号</th>
		                <th data-options="field:'bbb',width:'180'">客户名称</th>
		                 <th data-options="field:'ccc',width:'140'">媒体名称</th>
		                <th data-options="field:'ddd',align:'right',width:'120'">执行成本</th>
		                <th data-options="field:'eee',align:'right',width:'120'">已付款金额</th>
		                <th data-options="field:'fff',align:'right',width:'120'">已付款未到票</th>
		                <th data-options="field:'ggg',align:'right',width:'180'">上次收票金额</th>
		                <th data-options="field:'hhh',align:'right',width:'180'">本次转入票金额</th>
		                <th data-options="field:'iii',align:'right',width:'180'">本次实际收票金额</th>
					</tr>
       			 </thead>		   
				</table>
				<div id="toolbar2" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn2">删除</a>
					</div>
				</div>
				<div class="btn_div">
       				 <input type="text" name="receive_pids" id="receive_pids" /><input type="text" name="pay_pids" id="pay_pids"/><input type="text" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="finance_receive_invoice_transfer"/><input type="button" value="提 交" class="btn_sub" id="submitbtn" />
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
	//提交按钮
	$("#submitb").click(function(){
		//应付金额
		$("#payment_amount_plan").removeClass("validate[optional,custom[money]]");
		$("#payment_amount_plan").addClass("validate[required,custom[money]]");

		//付款时间
		$("#payment_date").addClass("validate[required]");

		//action
		$("#action").val("payment_person_apply");
		$("#formID").submit();
	});

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


	
	$("#sbtn").click(function(){
		 dosearch(1);
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
	if($.trim($("#search_executive").val())=="" && $.trim($("#cusname").val())=="" && $.trim($("#projectname").val())=="" && $.trim($("#medianame").val())==""){
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

function pidmove(){
	var sel = false;
	$(":checkbox").each(function(index, element) {
		if ($(this).attr("checked")=="checked" && $(this).val() !="1"){
			var nowpids = $("#pids").val();
			if(nowpids.indexOf("," + $(this).val() + ",") == -1){
				if(!sel){
					sel = true;
				}
				$("#pids").val(nowpids + $(this).val() + ",");
			}
		}
	});
}

function removepid(v,pid){
	$(v).parent().parent().remove();
	var nowpids = $("#pids").val();
	$("#pids").val(nowpids.replace("," + pid + ",",","));
}

function selectall(obj){
	var checked = false;
	if(obj.checked){
		checked = true;
	}
	$("input[name='selexe']").each(function(){
		$(this).attr("checked",checked);
	});
}

function check_select_all(obj){
	if(!obj.checked){
		$("#selall").attr("checked",false);
	}else{
		var sall = true;
		$("input[name='selexe']").each(function(){
			if($(this).attr("checked")!="checked" && sall){
				sall = false;
				return false;
			}
		});
		if(sall){
			$("#selall").attr("checked",sall);
		}
	}
}

function openit(pid){
	var id = "tr_" + pid;
	$("#" + id).toggle();
}


</script>
</body>
</html>
