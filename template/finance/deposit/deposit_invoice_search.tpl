<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/tablesorter.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 开票管理</div>
		<div class="tab" id="tab">
			<ul>
            	<li><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=1">待审核待打印</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=3">已打印待归档</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=2">当月已审核</a></li>
		        <li class="on"><a>开票信息查询</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoice_import">开票信息导入</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivableslist">当月保证金收款列表</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivables">保证金收款登记</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivables_search">保证金收款信息查询</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivables_import">保证金收款信息导入</a></li>
			</ul>
		</div>
        <div class="listform fix">
        	<table width="100%" class="tabin">
                <tr>
                    <td >
                        &nbsp;&nbsp;起始：
                        <input type="text" class="text Wdate" name="starttime" id="starttime" style="width:100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" value="[STARTTIME]" /> 
                        &nbsp;终止：
                        <input type="text" class="text Wdate" name="endtime" id="endtime" style="width:100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" value="[ENDTIME]" />
                        &nbsp;&nbsp;关键字：<input type="text" name="search" id="search" style="height:20px;" value="[SEARCH]"/>
                        &nbsp;&nbsp;<input type="button" id="dosearch" value="搜 索" class="btn"/>
                         &nbsp;&nbsp;<input type="button" id="doexport" value="导 出" class="btn"/>
                    </td>
                </tr>
            </table>
             <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="ilist">
		      	<thead>
		        <tr>
		          <th width="50"><input type="checkbox" id="selectall"/> 全选</th>
		          <th>开票日期</th>
		           <th>开票抬头</th>
		          <th>开票金额</th>
		          <th>发票号码</th>
		          <th>开票类型</th>
		          <th>申请人</th>
		          <th>归档人</th>
		          <th>操作</th>
		        </tr>
		        </thead>
		        <tbody>
		        [INVOICELIST]
		        </tbody>
      		</table>
      		<table>
      			<tr>
      			<td><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="deposit_invoice_export"/>
      			</td>
      			</tr>
      		</table>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
$(document).ready(function() {
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
	$("#ilist").tablesorter({
		headers:{
			0:{sorter:false},
			3:{sorter:"cu"}
		}
	});
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });

	$("#dosearch").click(function(){
		location.href = base_url + "finance/deposit/?o=deposit_invoice_search&starttime=" + $("#starttime").val() + "&endtime=" + $("#endtime").val() + "&search=" + $("#search").val();
	});
	//全选
	$("#selectall").click(function(){
		var checked = false;
		if($(this).attr("checked")=="checked"){
			checked = true;
		}
		$("input[name='selinvoice[]']").each(function(){
			$(this).attr("checked",checked);
		});
	});

	$("#doexport").click(function(){
		$("#formID").validationEngine("attach",{ 
 			validationEventTrigger: "",
 			autoHidePrompt:true,
 			autoHideDelay:3000,
 		    success: false,
 		    promptPosition:"bottomRight", 
 		    scroll:false
 		});
		$("#formID").submit();
	});
});

function check_select_all(obj){
	if(!obj.checked){
		$("#selectall").attr("checked",false);
	}else{
		var sall = true;
		$("input[name='selinvoice[]']").each(function(){
			if($(this).attr("checked")!="checked" && sall){
				sall = false;
				return false;
			}
		});
		if(sall){
			$("#selectall").attr("checked",sall);
		}
	}
}
</script>
</body>
</html>
