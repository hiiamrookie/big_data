<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 会议室预定</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link rel='stylesheet' type='text/css' href='[BASE_URL]booking/reset.css' />
<link rel='stylesheet' type='text/css' href='[BASE_URL]booking/jquery-ui.css' />
<link rel='stylesheet' type='text/css' href='[BASE_URL]booking/jquery.weekcalendar.css' />
<link rel='stylesheet' type='text/css' href='[BASE_URL]booking/demo.css' />
<link rel="stylesheet" type="text/css" href="[BASE_URL]css/style.css" media="screen" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">
    	[TOP]
    </div>
	<div id="content" class="fix">
		<div class="crumbs">会议室预定：所有会议室备有手写板和电话会议系统（需申领），9F/A、8F/A提供投影仪，9F/B、8F/B、8F/C提供迷你投影仪（需申领） </div>
		<div class="tab" id="tab">
			<ul>
				[CLASS_TYPE]
			</ul>
		</div>
        
        <h1 id="[TYPE]"></h1>
        <div id='calendar'></div>
        <div id="event_edit_container">
            <form>
                <input type="hidden" />
                <ul>
                    <li>
                        <span>日期: </span><span class="date_holder"></span> 
                    </li>
                    <li>
                        <label for="start">开始时间</label><select name="start"><option value="">选择开始时间</option></select>
                    </li>
                    <li>
                        <label for="end">结束时间</label><select name="end"><option value="">选择结束时间</option></select>
                    </li>
                    <li>
                        <label for="title">会议内容</label><input type="text" name="title"/>
                    </li>
                    <li>
                    	<label for="is_tel_meeting"><input type="checkbox" name="is_tel_meeting" id="is_tel_meeting" value="1"/>&nbsp;&nbsp;电话会议</label>
                    </li>
                    <li>
                    	<label for="tel_meeting_type"><input type="radio" name="tel_meeting_type" disabled="disabled" value="1"/>&nbsp;&nbsp;两方&nbsp;&nbsp;<input type="radio" name="tel_meeting_type" disabled="disabled" value="2"/>&nbsp;&nbsp;多方</label>
                    </li>
                </ul>
            </form>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type='text/javascript' src='[BASE_URL]booking/jquery-ui.min.js'></script>
<script type='text/javascript' src='[BASE_URL]booking/jquery.weekcalendar.js'></script>
<script type='text/javascript' src='[BASE_URL]booking/demo.js?v=1'></script>
<script type="text/javascript">
var vcode = '[VCODE]';
$(document).ready(function() {
	$("#SYS_Wdate").text(showdate());
	//$(".etable tr:odd").addClass("bw");
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
	}).eq(menu_b).click();	

	$("#is_tel_meeting").click(function(){
		if($(this).attr("checked")=="checked"){
			$("input[name='tel_meeting_type']").attr("disabled",false);
		}else{
			$("input[name='tel_meeting_type']").attr("disabled",true);
		}
	});
});

//显示系统日期
function showdate() 
{
  var now=new Date();
  var year = now.getFullYear();
  var month = now.getMonth()+1;
  var day = now.getDate();
  var dayname;
  
  if (now.getDay() == 0) dayname="星期日";
  if (now.getDay() == 1) dayname="星期一";
  if (now.getDay() == 2) dayname="星期二";
  if (now.getDay() == 3) dayname="星期三";
  if (now.getDay() == 4) dayname="星期四";
  if (now.getDay() == 5) dayname="星期五";
  if (now.getDay() == 6) dayname="星期六";
  
  return year+"年"+month+"月"+day+"日"+" "+dayname;
}

</script>
</body>
</html>
