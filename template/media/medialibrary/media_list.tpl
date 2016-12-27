<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 媒体数据管理</title>
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
    	<div class="crumbs">媒体数据管理 - 媒体库信息</div>
    	<div class="tab" id="tab" style="height:30px">
        	<ul>
    			<li class="on"><a href="?o=mtlist">媒体库列表</a></li>
				<li><a href="?o=mtadd">新建媒体信息</a></li>
            </ul>
    	</div>
    	<div class="listform fix">
        	<table class="etable" cellpadding="0" cellspacing="0" border="0" width="950" id="mlist">
        		<thead>
            	<tr>
                  <th>编号</th>
                  <th>媒体全称</th>
                  <th>媒体中文简称</th>
                  <th>媒体英文简称</th>
                  <th>URL</th>
                  <th>媒体联络人</th>
                  <th>联系方式</th>
                  <th>代理资质信息</th>
                  <th>操作</th>
                </tr>
                </thead>
               <tbody>
                [MEDIALIST]
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
<script type="text/javascript">
$(document).ready(function() {
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
	}).eq(menu_m).click();
	
	$("#mlist").tablesorter();
	
	$(".etable tr ").mousemove(function(){ $(this).addClass("bw"); });
	$(".etable tr ").mouseout(function(){ $(this).removeClass("bw"); });
});
</script>
</body>
</html>
