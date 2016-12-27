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
		<div class="crumbs">财务管理 - 财务信息查询</div>
		<div class="tab">
      		<ul>
        		<li><a href="[BASE_URL]finance/?o=custominfosearch">客户财务信息查询</a></li>
        		<li class="on"><a>执行单财务信息查询</a></li>
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
      			<tr>
      				<td>
      					媒体名称&nbsp;<input type="text" name="medianame" id="medianame" style="height:20px;"/>&nbsp;&nbsp;
      					客户名称&nbsp;<input type="text" name="cusname" id="cusname" style="height:20px;"/>&nbsp;&nbsp;
      					执行单号&nbsp;<input type="text" name="pid" id="pid" style="height:20px;"/>&nbsp;&nbsp;	
      					项目名称&nbsp;<input type="text" name="projectname" id="projectname" style="height:20px;"/>&nbsp;&nbsp;
      					<input type="button" value="&nbsp;搜索&nbsp;" class="btn" id="sbtn"/>
      				</td>
      			</tr>
      		</table>
      		<p/>
      		<table id="dg" style="width:100%"></table>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {	
	$("#dg").datagrid({
		title:'执行单财务信息查询结果',
		autoRowHeight:true,
		striped:true,
		pagination:true,
		pageList:[10,30,50],
		columns:[[
			{field:'a',width:'100',title:"执行单号"},
			{field:'b',width:'200',title:"项目名称"},
			{field:'c',width:'200',title:"客户名称"},
			{field:'d',width:'100',align:'right',title:"执行金额"},
			{field:'e',width:'100',align:'right',title:"执行成本"},
			{field:'f',width:'200',title:"媒体"},
			{field:'g',width:'100',align:'right',title:"已收客户款"},
			{field:'h',width:'100',align:'right',title:"已开票"},
			{field:'i',width:'100',align:'right',title:"已付媒体款"},
			{field:'j',width:'100',align:'right',title:"已收媒体票"}
		]]
	});

	$("#sbtn").click(function(){
		 dosearch(1);
	});
});

function dosearch(page){
	if($.trim($("#medianame").val())=="" && $.trim($("#cusname").val())=="" && $.trim($("#pid").val())=="" && $.trim($("#projectname").val())==""){
		alert("请至少输入一个搜索条件");
	}else{
		var url = base_url + "get_data.php?action=getPidFinanceInfo&medianame=" + encodeURI($.trim($("#medianame").val())) + "&cusname=" + encodeURI($.trim($("#cusname").val())) + "&pid=" + encodeURI($.trim($("#pid").val())) + "&projectname=" +  encodeURI($.trim($("#projectname").val()));
		$("#dg").datagrid({
			url:url
		});
	}
}

</script>
</body>
</html>
