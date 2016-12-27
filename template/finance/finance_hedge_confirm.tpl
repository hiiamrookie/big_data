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
		<div class="crumbs">财务管理 - 收付对冲</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>确认收付对冲</a></li>
        		<li><a href="[BASE_URL]finance/?o=hedge_list">收付对冲列表</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td>
      					<input type="radio" name="hedgetype" value="1" checked/>&nbsp;收&nbsp;&nbsp;<input type="radio" name="hedgetype" value="2"/>&nbsp;付&nbsp;&nbsp;
      					媒体名称&nbsp;<input type="text" name="medianame" id="medianame" style="height:20px;"/>&nbsp;&nbsp;
      					客户名称&nbsp;<input type="text" name="cusname" id="cusname" style="height:20px;"/>&nbsp;&nbsp;
      					执行单号&nbsp;<input type="text" name="pid" id="pid" style="height:20px;"/>&nbsp;&nbsp;
      					项目名称&nbsp;<input type="text" name="projectname" id="projectname" style="height:20px;"/>&nbsp;&nbsp;
      					<input type="button" value="&nbsp;搜索&nbsp;" class="btn" id="searchbtn"/>
      				</td>
      			</tr>
      		</table>
      		<!-- 所选【收】数据 -->
      		<p/>
				<table id="dg1" style="width:98%;"></table>
				<div id="toolbar1" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn1">删除</a>
					</div>
				</div>
				<!-- 所选【付】数据 -->
				<p/>
      		<table id="dg2" style="width:98%;"></table>
				<div id="toolbar2" style="padding:5px;height:auto">
					<div style="margin-bottom:5px">
						<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" id="cancelbtn2">删除</a>
					</div>
				</div>
				
				<div class="btn_div">
       				 <input type="hidden" name="id" id="id" value="[ID]"/><input type="hidden" name="receive_pids" id="receive_pids" value="[RECEIVEPIDS]"/><input type="hidden" name="pay_pids" id="pay_pids" value="[PAYPIDS]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="finance_hedge_confirm"/><input type="button" value="提 交" class="btn_sub" id="submitbtn" />
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
	$("#cancelbtn1").click(function(){
		var rows = $('#dg1').datagrid('getSelections');
		if(rows.length == 0){
			alert("请选择至少一条记录");
		}else{
			var receive_pids = $("#receive_pids").val();
			for(var i=0;i<rows.length;i++){
				//dg中删除行
				var index = $('#dg1').datagrid('getRowIndex', rows[i]);
				$('#dg1').datagrid('deleteRow', index);  
				$("#receive_pids").val(receive_pids.replace("," + rows[i].xx + ",",","));
			}
		}
	});

	//付 删除
	$("#cancelbtn2").click(function(){
		var rows = $('#dg2').datagrid('getSelections');
		if(rows.length == 0){
			alert("请选择至少一条记录");
		}else{
			var pay_pids = $("#pay_pids").val();
			for(var i=0;i<rows.length;i++){
				//dg中删除行
				var index = $('#dg2').datagrid('getRowIndex', rows[i]);
				$('#dg2').datagrid('deleteRow', index);  
				$("#pay_pids").val(pay_pids.replace("," + rows[i].xxx + ",",","));
			}
		}
	});
	
	$("#addbtn").click(function(){
		var rows = $('#searchdg').datagrid('getSelections');
		if(rows.length == 0){
			alert("请选择至少一条记录");
		}else{
			var hedgetype = $('input[name="hedgetype"]:checked').val();
			if(hedgetype == 1){
				//收
				var receive_pids = $("#receive_pids").val();
				var add_arr = new Array();
				for(var i=0;i<rows.length;i++){
					if(receive_pids.indexOf("," + rows[i].a + ",") == -1){
						add_arr.push(rows[i].a);
						var r = new Array();
						r["xx"] = rows[i].a;
						r["aa"] = rows[i].a;
						r["bb"] = rows[i].b;
						r["cc"] = rows[i].c;
						r["dd"] = rows[i].d;
						r["ee"] = rows[i].e;
						r["ff"] = rows[i].f;
						r["gg"] = '<input type="text" name="receive_' + rows[i].a + '" id="receive_' + rows[i].a + '" style="height:20px;" class="validate[required,max[' + rows[i].e + ']]" value="' + rows[i].e + '"/>';
						$('#dg1').datagrid('appendRow',r);
					}
				}
				add_arr = add_arr.join(",") + ",";
				if(receive_pids==""){
					add_arr = "," + add_arr;
				}else{
					add_arr = receive_pids + add_arr;
				}
				$("#receive_pids").val(add_arr);
			}else{
				//付
				var pay_pids = $("#pay_pids").val();
				var add_arr = new Array();
				for(var i=0;i<rows.length;i++){
					if(pay_pids.indexOf("," + rows[i].a + ",") == -1){
						add_arr.push(rows[i].a);
						var r = new Array();
						r["xxx"] = rows[i].a;
						r["aaa"] = rows[i].a;
						r["bbb"] = rows[i].b;
						r["ccc"] = rows[i].c;
						r["ddd"] = rows[i].d;
						r["eee"] = rows[i].e;
						r["fff"] = rows[i].f;
						r["ggg"] = '<input type="text" name="pay_' + rows[i].a + '" id="pay_' + rows[i].a + '" style="height:20px;" class="validate[required,max[' + rows[i].e + ']]" value="' + rows[i].e + '"/>';
						r["iii"] = rows[i].h;
						r["jjj"] = rows[i].i;
						$('#dg2').datagrid('appendRow',r);
					}
				}
				add_arr = add_arr.join(",") + ",";
				if(pay_pids==""){
					add_arr = "," + add_arr;
				}else{
					add_arr = pay_pids + add_arr;
				}
				$("#pay_pids").val(add_arr);
			}


			
			$("#dd").dialog({
			    closed: true
			});
		}
	});
	
	$('#dd').dialog({
	    title: '收付搜索',
	    width: window.screen.width * 2 / 3,
	    height: window.screen.height / 2,
	    closed: true,
	    cache : false,
	    modal : true
	});

	$('#searchdg').datagrid({
	    toolbar: '#tb',
	    pagination : true,
	    striped : true,
	    pageList : [10,20,30],
	    fit : true,
	});
	
	$("#searchbtn").click(function(){
		var hedgetype = $('input[name="hedgetype"]:checked').val();
		var url = base_url + "get_data.php";
		if(hedgetype=="1"){
			var columns = [[
					        {field:'x',title:'',checkbox:true},
					        {field:'a',title:'执行单号'},
					        {field:'b',title:'客户名称'},
					        {field:'c',title:'项目名称'},
					        {field:'d',title:'执行金额',align:'right'},
					        {field:'e',title:'到款金额',align:'right'},
					        {field:'f',title:'开票金额',align:'right'}
					    ]];
		  	url += "?action=getHedgeReceive";
		}else{
			var columns = [[
					        {field:'x',title:'',checkbox:true},
					        {field:'a',title:'执行单号'},
					        {field:'b',title:'客户名称'},
					        {field:'c',title:'项目名称'},
					        {field:'g',title:'媒体名称'},
					        {field:'d',title:'执行金额',align:'right'},
					        {field:'e',title:'付款金额',align:'right'},
					        {field:'f',title:'到票金额',align:'right'}       
					    ]];
			url += "?action=getHedgePay";
		}
		if($.trim($("#medianame").val()) !=""){
			url += "&medianame=" + $.trim($("#medianame").val());
		}
		if($.trim($("#cusname").val()) !=""){
			url += "&cusname=" + $.trim($("#cusname").val());
		}
		if($.trim($("#pid").val()) !=""){
			url += "&pid=" + $.trim($("#pid").val());
		}
		if($.trim($("#projectname").val()) !=""){
			url += "&projectname=" + $.trim($("#projectname").val());
		}

		
		$('#searchdg').datagrid({
			 columns:columns,
			 url:url
		});
		$("#dd").dialog({
		    closed: false
		});
	});



	
	//提交按钮
	$("#submitbtn").click(function(){
		var receive_pids = $("#receive_pids").val();
		var pay_pids = $("#pay_pids").val();
		receive_pids = receive_pids.split(",");
		pay_pids = pay_pids.split(",");
		var sum_receive = 0;
		var sum_pay = 0;
		for(var i=0;i<receive_pids.length;i++){
			if(receive_pids[i] != ""){
				var id = "receive_" + receive_pids[i];
				sum_receive += $("#" + id).val();
			}
		}

		for(var i=0;i<pay_pids.length;i++){
			if(pay_pids[i] != ""){
				var id = "pay_" + pay_pids[i];
				sum_pay += $("#" + id).val();
			}
		}
		if(Number(sum_receive) !=Number(sum_pay) ){
			alert("收付对冲金额必须一致");
		}else{
			$("#formID").submit();
		}
	});

	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"topLeft", 
	    scroll:false
	});

	$("#dg1").datagrid({
		title:'所选【收】数据',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		toolbar:'#toolbar1',
		url : base_url + "get_data.php?action=getHedgeReceiveByPids&id=" + encodeURI($("#id").val()) + "&pids=" + encodeURI($("#receive_pids").val()),
		columns:[[
			{field:'xx',checkbox:true},
			{field:'aa',width:'120',title:"执行单号"},
			{field:'bb',width:'180',title:"客户名称"},
			{field:'cc',width:'140',title:"项目名称"},
			{field:'dd',align:'right',width:'120',title:"执行金额"},
			{field:'ee',align:'right',width:'120',title:"到款金额"},
			{field:'ff',align:'right',width:'120',title:"开票金额"},
			{field:'gg',align:'right',width:'180',title:"【收】金额"},
		]]
	});

	$("#dg2").datagrid({
		title:'所选【付】数据',
		autoRowHeight:true,
		striped:true,
		rownumbers:true,
		toolbar:'#toolbar2',
		url : base_url + "get_data.php?action=getHedgePayByPids&id=" + encodeURI($("#id").val()) + "&pids=" + encodeURI($("#pay_pids").val()),
		columns:[[
			{field:'xxx',checkbox:true},
			{field:'aaa',width:'120',title:"执行单号"},
			{field:'bbb',width:'180',title:"客户名称"},
			 {field:'ccc',width:'140',title:"项目名称"},
			 {field:'hhh',width:'140',title:"媒体名称"},
			{field:'ddd',align:'right',width:'120',title:"执行金额"},
			{field:'eee',align:'right',width:'120',title:"付款金额"},
			{field:'fff',align:'right',width:'120',title:"到票金额"},
			{field:'ggg',align:'right',width:'180',title:"【付】金额"},
			{field:'iii',align:'right',width:'120',title:"已执行未付款金额"},
			{field:'jjj',align:'right',width:'120',title:"已付款未到票"},
		]]
	});
});
</script>
</body>
</html>
