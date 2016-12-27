<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 客户合同管理系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">
		[TOP]
	</div>
	<div id="content" class="fix">
    	<div class="crumbs">合同管理系统 - 修改客户合同 </div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a href="#"><span style=" font-weight:bold">[CID]</span></a></li>
                <li><a>流转状态</a></li>
			</ul>
		</div>
        <div class="box">
            <div class="publicform fix">
            	<form id="formID" method="post" action="[BASE_URL]contract_cus/action.php" target="post_frame">
                [EDITPAGE]
                <div class="btn_div"><input type="hidden" name="cid" value="[CID]"/><input type="hidden" name="action" value="contract_update"/><input type="hidden" name="vcode" value="[VCODE]"/><input type="submit" id="submit" value="提 交" class="btn_sub" /></div>
            	</form>
            	<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
            </div>
         </div>
         <div class="box">
            <div class="listform" style="padding:0">
               [LOGLIST]
            </div>
        </div>   
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.autocomplete.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]contract_cus/contract_cus.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var CID="[CID]";
var vcode = "[VCODE]";
var base_url = '[BASE_URL]';
$(document).ready(function() {
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true)	$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	$("#tab li").click(function(){
		$(this).addClass("on").siblings("li").removeClass();
		$(".box:eq("+$(this).index()+")").show().siblings(".box").hide();
	}).eq(0).click();
	
//	$("#submit").click(function(){ docontract_cus("edit"); });
	
	$("#addbzj").click(addbzj);
	
	$("#addcf1").click(addcf1);
	
	$("#addcf2").click(addcf2);
	
	$("#adddaili").click(adddaili);
	
	$("input[name=type1]").click(showdaili);
	
	$("#isfmkcid").click(fmkcid);
	
	//从地区选择部门
    $("#city").live("change",function(){ 
        $("#dep").html('<option value="">请选择部门</option>');
        $("#team").html('<option value="">请选择团队</option>');
        getdepsbycity(this,base_url,vcode);
    });

    //从部门选择团队
    $("#dep").live("change",function(){ 
    	$("#team").html('<option value="">请选择团队</option>');
        getteamsbydep(this,base_url,vcode); 
        getdtuserlist("contactperson",base_url,vcode);
    });

    //团队选择队员
    $("#team").live("change",function(){ 
        getdtuserlist("contactperson",base_url,vcode);
    });

    $("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

    $("#customer").hide();
	$("#customershow").html($("#customer").find("option:selected").text());
});

function select_customer(obj){
	$.ajax({
		   type: "POST",
		   url: "do.php",
		   cache:"false",
		   data: "action=select_customer&cusname=" + obj.value + "&t=" + Math.random() + "&vcode=" + vcode,
		   dataType:'text',
		   async: false,
		   success: function(msg){
			   if(msg != "-1"){
					$("#customer").val(msg);
					$("#customer").hide();
					$("#customershow").html($("#customer").find("option:selected").text());
				}else{
					$("#customer").val("");
					$("#customer").show();
					$("#customershow").html("");
				}
		   },
	 	   error: function(e){
	 		   alert("系统客户名称显示异常");
	 	   }
	});	
}
</script>
</body>
</html>
