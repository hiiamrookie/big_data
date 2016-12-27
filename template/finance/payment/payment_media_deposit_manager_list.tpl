<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 执行单系统</title>
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
		<div class="crumbs">财务管理 - 媒体付款申请</div>
		<div class="tab" id="tab" style="height:30px">
        	<ul>
        		<li><a href="[BASE_URL]finance/payment/?o=person_apply_manager">个人申请付合同款列表</a></li>
				<li><a href="[BASE_URL]finance/payment/?o=person_deposit_apply_manager">个人申请付保证金款列表</a></li>
        		<li><a href="[BASE_URL]finance/payment/?o=media_manager">媒体批量付款申请列表</a></li>
        		<li class="on"><a>媒体批量保证金付款申请列表</a></li>
			</ul>
		</div>
        <div class="listform fix">
            <table class="etable" cellpadding="0" cellspacing="0" border="0" id="example">
            	<thead>
	                <tr>
	                	<th>类型</th>
	               		 <th>付款单号</th>
	                	<th>约定付款时间</th>
	                    <th>媒体</th>
	                    <th>应付金额</th>
	                    <th>实付金额</th>
	                    <th>状态</th>
	                     <th>操作</th>
	                </tr>
	             </thead>
	             <tbody>
	             [PAYMENTMEDIALIST]
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
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.autocomplete.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
var vcode = "[VCODE]";
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
			4:{sorter:"cu"},
			5:{sorter:"cu"}
		}
	});
	
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });
});
</script>
</body>
</html>
