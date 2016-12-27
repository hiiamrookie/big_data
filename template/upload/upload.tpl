<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>上传排期测试</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">
		[TOP]
	</div>

	<div id="content" class="fix">
		<div class="crumbs">排期上传测试</span></div>
		<div class="tab">
			<ul>
				<li class="on"><a>排期上传测试</a></li>
			</ul>
		</div>
        
		<div class="publicform fix">
			<table class="sbd1">
                <tr width="100">
                    <td>排期EXCEL上传：</td>
                    <td><input type="file" name="inputExcel" id="inputExcel" style="width:300px;" /> <input type="button" id="submit" value="上传"/></td>
                </tr>
                <tr>
                    <td>测试结果：</td>
                    <td id="result"></td>
                </tr>
            </table>
		</div>
        
        <!--<div class="publicform fix">
			<table class="sbd1">
                <tr width="100">
                    <td>百度排期文件：</td>
                    <td><input type="file" name="inputExcel_baidu" id="inputExcel_baidu" style="width:300px;" /> <input type="button" id="submit_baidu" value="上传"/></td>
                </tr>
                <tr>
                    <td>测试结果：</td>
                    <td id="result_baidu"></td>
                </tr>
            </table>
		</div>-->
        
        <div class="publicform fix">
			<table class="sbd1">
                <tr width="100">
                    <td>外包EXCEL上传：</td>
                    <td><input type="file" name="inputExcel_out" id="inputExcel_out" style="width:300px;" /> <input type="button" id="submit_out" value="上传"/></td>
                </tr>
                <tr>
                    <td>测试结果：</td>
                    <td id="result_out"></td>
                </tr>
            </table>
		</div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
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
	
	$("#submit").live("click",function(){uploadfile();});
	
	$("#submit_baidu").live("click",function(){uploadfile_baidu();});
	
	$("#submit_out").live("click",function(){uploadfile_out();});
});

//上传其他排期
function uploadfile()
{
  if ($("#inputExcel").val()=="") { alert("请选择要上传的文件！"); return; }
 
   
  //得到上传文件的ID 
  var fileid=$("#inputExcel").attr("id");
  //得到文件名
  var pos = $("#inputExcel").val().lastIndexOf("\\")*1;
  var filename=$("#inputExcel").val().substring(pos+1);
  
  $("#result").html("正在上传.....请稍后！");

  $.ajaxFileUpload
  ({
	url:"./upload_media.php?t="+Math.random(),
	secureuri:false,
	fileElementId:fileid,
	dataType: "text",
	error: function (data,status,e){ alert("网络连接有误，请重试！！"); },
	success: function (data, status)
	{
	  alert(data);
	  $("#result").html(data);
	}
  });  
}

//上传百度排期
function uploadfile_baidu()
{
  if ($("#inputExcel_baidu").val()=="") { alert("请选择要上传的文件！"); return; }
 
   
  //得到上传文件的ID 
  var fileid=$("#inputExcel_baidu").attr("id");
  //得到文件名
  var pos = $("#inputExcel_baidu").val().lastIndexOf("\\")*1;
  var filename=$("#inputExcel_baidu").val().substring(pos+1);
  
  $("#result_baidu").html("正在上传.....请稍后！");

  $.ajaxFileUpload
  ({
	url:"./upload_baidu.php",
	secureuri:false,
	fileElementId:fileid,
	dataType: "text",
	error: function (data,status,e){ alert("网络连接有误，请重试！！"); },
	success: function (data, status)
	{
	  $("#result_baidu").html(data);
	}
  });  
}

//上传外包
function uploadfile_out()
{
  if ($("#inputExcel_out").val()=="") { alert("请选择要上传的文件！"); return; }
 
   
  //得到上传文件的ID 
  var fileid=$("#inputExcel_out").attr("id");
  //得到文件名
  var pos = $("#inputExcel_out").val().lastIndexOf("\\")*1;
  var filename=$("#inputExcel_out").val().substring(pos+1);
  
  $("#result_out").html("正在上传.....请稍后！");

  $.ajaxFileUpload
  ({
	url:"./upload_outsource.php",
	secureuri:false,
	fileElementId:fileid,
	dataType: "text",
	error: function (data,status,e){ alert("网络连接有误，请重试！！"); },
	success: function (data, status)
	{
	  //alert(data);
	  $("#result_out").html(data);
	}
  });  
}


</script>
</body>
</html>
