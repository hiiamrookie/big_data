<!DOCTYPE HTML>
<html>
<head>
<title> 首页</title>
<meta charset="utf-8"/>
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/tablesorter.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/default/easyui.css"/>
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/icon.css"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">待处理事项：</div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a>待处理执行单 (<span>[COUNT_EXECUTIVE]</span>)</a></li>
				<li><a>待处理客户合同 (<span>[COUNT_CONTRACT_CUS]</span>)</a></li>
				[INVOICETAB]
				[DEPOSITTAB]
				[SUPPLIERAPPYAUDITTAB]
				[CONTRACTPAYMENTPERSONTAB]
				[OUTSOURCINGAUDITTAB]
				[PROJECTTAB]
			</ul>
		</div>
        <div class="undis">
            <div class="listform fix">
                <table class="etable" cellpadding="0" cellspacing="0" border="0" id="executive_list">
                	<thead>
                		<tr>
							<th width="5%">类型</th>
	                        <th width="10%">执行单号</th>
	                        <th width="25%">客户名称</th>
	                        <th width="25%">项目名称</th>
	                        <th width="10%">总金额</th>
	                        <th width="10%">总成本<font color="#0000FF">（预估）</font></th>
	                        <th width="10%">状态</th>
	                        <th width="5%">操作</th>
	                     </tr>
                    </thead>
                    <tbody>
                    [EXECUTIVELIST]
                    </tbody>
                </table>
            </div>
        </div>
        <div class="undis">
            <div class="listform fix">
                <table class="etable" cellpadding="0" cellspacing="0" border="0" width="950" id="contract_list">
                    <thead>
                		<tr>
	                      <th>是否归档</th>
	                      <th>合同单号</th>
	                      <th>申请时间</th>
	                      <th>合同类型</th>
	                      <th>合同名称</th>
	                      <th>客户名称</th>
	                      <th>合同金额</th>
	                      <th>审核状态</th>
	                      <th>操作</th>
                   	 	</tr>
                   	 </thead>
                   	 <tbody>
                    	[CONTRACTLIST]
                    </tbody>
                </table>
            </div>
        </div>
        [INVOICELIST]
        [DEPOSITLIST]
        [SUPPLIERAPPYAUDITLIST]
        [CONTRACTPAYMENTPERSONLIST]
        [OUTSOURCINGAUDITLIST]
        [PROJECTAUDITLIST]
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript">
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
	
	$("#executive_list").tablesorter({
		headers:{
			4:{sorter:"cu"},
			5:{sorter:"cu"}
		}
	});
	$("#contract_list").tablesorter({
		headers:{
			6:{sorter:"cu"}
		}
	});
	$("#invoice_list").tablesorter({
		headers:{
			2:{sorter:"cu"}
		}
	});
	$("#deposit_list").tablesorter({
		headers:{
			3:{sorter:"cu"}
		}
	});
	$("#supplier_list").tablesorter();
	
	$("#SYS_Wdate").text(showdate());

	$("#side_nav h2").click(function(){
		if($(this).hasClass("current")){return;}
		else{
			$("#side_nav h2").removeClass("current");
			$("#side_nav ul").removeClass("pane");
			$("#side_nav ul").slideUp("fast");
			$(this).addClass("current");
			$(this).next("ul").addClass("pane");
			$(this).next("ul").slideDown(0);
		}
	}).eq(menu_p).click();
	
	$("#tab li").click(function(){
		$(this).addClass("on").siblings("li").removeClass();
		$(".undis:eq("+$(this).index()+")").show().siblings(".undis").hide();
	}).eq(0).click();
	$(".etable tr").live("mouseover",function () { $(this).addClass("bw"); });
	$(".etable tr").live("mouseout",function () {$(this).removeClass("bw"); });

	[PAYMENTMESSAGE]
});
</script>
</body>
</html>
