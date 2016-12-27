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
		<div class="crumbs">财务管理</div>
		<div class="tab" id="tab">
			<ul>
				<li><a href="?o=supplierlist">供应商信息列表</a></li>
        		<li><a href="?o=supplierimport">供应商信息导入</a></li>
        		<li><a href="?o=supplierindustry">新建客户行业分类</a></li>
        		<li><a href="?o=supplierindustrylist">客户行业分类列表</a></li>
        		<li><a href="?o=suppliercategory">新建供应商产品分类</a></li>
        		<li class="on"><a>供应商产品分类列表</a></li>
        		<li><a href="?o=supplier_export">信息导出</a></li>
			</ul>
		</div>
        <div class="listform fix">
  	<table width="100%" class="tabin">
                <tr>
                    <td >
                        &nbsp;&nbsp;关键字：<input type="text" name="search" id="search" style="height:20px;" value="[SEARCH]"/>
                        &nbsp;&nbsp;<input type="button" id="dosearch" value="搜 索" class="btn"/>
                    </td>
                </tr>
    </table>
    <table class="etable" cellpadding="0" cellspacing="0" border="0" width="950" id="flist">
    	<thead>
        <tr>
          <th width="50">编号</th>
          <th>供应商产品分类名称</th>
          <th>所属供应商名称</th>
          <!--th>返点率(%)</th>
          <th>适用时间段</th-->
          <th>状态</th>
          <th>操作</th>
        </tr>
        </thead>
        <tbody>
        [CATEGORYLIST]
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
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {
	$("#flist").tablesorter();
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });

	$("#dosearch").click(function(){
		location.href = base_url + "finance/supplier/?o=suppliercategorylist&search=" + $("#search").val();
	});
});

function cancel(id,recover){
	var al = recover==1 ? "恢复" : "撤销";
	if(window.confirm("确认" + al + "？")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:false,
			   data: "action=supplier_category_cancel&id=" + id + "&recover=" +  recover + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				  alert(msg);
			   },
		 	   error: function(e){
		 		   alert(al + "错误，请联系系统管理员！");
		 	   }
		});
		location.href="[BASE_URL]finance/supplier/?o=suppliercategorylist";
	}
}
</script>
</body>
</html>
