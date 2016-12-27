<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理系统</title>
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
  <div class="nav_top"> [TOP] </div>
  <div id="content" class="fix">
    <div class="crumbs">财务管理 - 开票管理</div>
    <div class="tab">
      <ul>
        <li [ON1]><a href="?o=invoicelist&d=1">待审核待打印</a></li>
        <li [ON3]><a href="?o=invoicelist&d=3">已打印待归档</a></li>
        <li [ON2]><a href="?o=invoicelist&d=2">当月已审核</a></li>
        <li><a href="?o=invoice_search">开票信息查询</a></li>
        <li><a href="?o=invoice_import">开票信息导入</a></li>
      </ul>
    </div>
    <div class="listform fix">
    	[SEARCHBAR]
      <table class="etable" cellpadding="0" cellspacing="0" border="0" id="ilist">
      	<thead>
      	[LISTTITLE]
        </thead>
        <tbody>
        [INVOICELIST]
        </tbody>
      </table>
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
			3:{sorter:"cu"}
		}
	});
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });

	$("#searchbtn").click(function(){
		location.href = base_url + "finance/invoice/?o=invoicelist&d=2&search=" + $("#search").val();
	});
	
	$("#expbtn").click(function(){
		$("#formID").submit();
	});
});

function simpleReject(id){
	if(window.confirm("确定驳回已审核的开票信息")){
		$.ajax({
			   type: "POST",
			   url: "do.php",
			   cache:"false",
			   data: "action=simpleReject&id=" + id + "&t=" + Math.random() + "&vcode=" + vcode,
			   dataType:'text',
			   async: false,
			   success: function(msg){
				   if(msg != "1"){
						alert(msg);
					}else{
						alert("驳回发票信息成功");
					}

					location.href = base_url + "finance/invoice/?o=invoicelist&d=3";
			   },
		 	   error: function(e){
		 		   alert("驳回发票信息失败");
		 	   }
		});	
	}
}
</script>
</body>
</html>
