<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/tablesorter.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 返点开票</div>
		<div class="tab">
      		<ul>
      			<li><a href="[BASE_URL]finance/rebate/?o=apply_invoice">申请返点开票</a></li>
        		<li class="on"><a>已申请返点开票列表</a></li>
      		</ul>
    	</div>
    	<div class="listform fix">
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="example">
            	<thead>
	                <tr>
	                    <th>发起时间</th>
	                    <th>媒体名称</th>
	                    <th>开票金额</th>
	                     <th>开票类型</th>
	                    <th>开票抬头</th>
	                    <th>状态</th>
	                    <th>操作</th>
	                </tr>
	             </thead>
	             <tbody>
	             [PAYMENTLIST]
                </tbody>
            </table>
            <div class="page_nav">
                <u>总记录：[ALLCOUNTS] 条 </u>
                <span class="page_nav_next">[NEXT]</span>
                <span class="page_nav_prev">[PREV]</span>
                <span class="page_nav_next">[COUNTS]</span>
            </div>
        </div>
  	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script src="[BASE_URL]script/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {	
	$.tablesorter.addParser({
		id: "day", //指定一个唯一的ID
		is: function(s){
		   return false;
		},
		format: function(s){
		   return s.toLowerCase().replace(/天/,0); //将中文换成数字
		},
		type: "numeric" //按数值排序
	});

	$.tablesorter.addParser({
		id: "cu", //指定一个唯一的ID
		is: function(s){
		   return false;
		},
		format: function(s){
		   return s.toLowerCase().replace(/￥/,"").replace(/,/g,"");
		},
		type: "numeric" //按数值排序
	});

	$("#example").tablesorter({
		headers:{
			2:{sorter:"cu"}
		}
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

function transfer(type,id){
	if(window.confirm("确定申请" + (type=="receive2pay" ? "应收转应付" : "应付转应收") + "?")){
		$.ajax({
			   type: "post",
			   url: base_url + "finance/rebate/do.php",
			   cache:"false",
			   data: "action=rebate_invoice_apply_transfer&type=" + type + "&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				  	if(msg == "1"){
						alert("申请成功，等待财务部处理");
					}else{
						aalert(msg);
					}
			   },
		 	   error: function(e){
		 		   alert("提交申请异常");
		 	   }
		});
	}
}
</script>
</body>
</html>
