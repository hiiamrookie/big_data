<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 开票申请</div>
		<div class="tab">
      		<ul>
        		<li class="on"><a>申请开票</a></li>
        		<li><a href="?o=mylist">已申请开票列表</a></li>
        		[INVOICETAB]
      		</ul>
    	</div>
    	<div class="publicform fix">
    		<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
       			<tr>
          			<td style="font-weight:bold; width:150px">关联执行单</td>
          			<td>
            			<input type="text" style="width:300px;height:20px;" id="search" name="search"/>
            			<input type="radio" name="billtype" value="1" checked="checked" /> 广告
            			<input type="radio" name="billtype" value="2" /> 服务
            			&nbsp;&nbsp;
            			<input type="button" value="搜 索" id="add" class="btn"/>
            			<div id="pidinfo">
            			</div>
          			</td>
        		</tr>
      			<tr>
          			<td style="font-weight:bold;width:150px">开票总金额</td>
          			<td><font color="#ff9933"><b><span id="invoceamount" style="font-size:15px">0.00</span> 元</b></font></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;">开票类型</td>
          			<td>
          				<input type="radio" value="1" id="type" name="type" checked="checked" onclick="showzp(1)"/> 普票 &nbsp;
            			<input type="radio" value="2" id="type" name="type" onclick="showzp(2)" /> 增票 &nbsp; 
            			<div id="showzp" style="display:none">
            				<br />
            				<div>纳税人识别号：&nbsp;<input type="text" style="width:350px;height:20px;" id="d1" name="d1"/></div>
                			<div>地址、电话：&nbsp;&nbsp;&nbsp;<input type="text" style="width:350px;height:20px;" id="d2" name="d2"/></div>
                			<div>开户行及账号：&nbsp;<input type="text" style="width:350px;height:20px;" id="d3" name="d3"/></div>
            			</div>
          			</td>
       	 		</tr>
        		<tr>
          			<td style="font-weight:bold">开票抬头</td>
          			<td><input type="text" class="validate[required,maxSize[200]]" style="width:300px;height:20px;" id="title" name="title"/></td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold">开票内容</td>
          			<td><input type="text" class="validate[required,maxSize[200]]" style="width:300px;height:20px;" id="cont" name="cont"/></td>
        		</tr>
		        <tr>
		          <td style="font-weight:bold">备注</td>
		          <td><textarea name="invoicecontent" id="invoicecontent" class="validate[optional,maxSize[500]] textarea" rows="3" style="width:300px;height:84px"></textarea></td>
		        </tr>
        		<tr>
        			<td style="font-weight:bold">选择应用流程</td>
           	 		<td>
               			<ul style="border:0;width:100%;" id="pcidlist">
               			[PROCESSLIST]
               			</ul>
               			<div style="clear:both;padding:4px;border:solid 1px #cecece;background:#efefef;"></div>
             		</td>
            	</tr>
      		</table>
      		<div class="btn_div">
        		<input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="invoice_apply"/><input type="hidden" name="pids" id="pids" value=","/><input type="submit" value="提 交" class="btn_sub" id="submit" />
      		</div>
      		</form>
      		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    	</div>
  	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
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
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';
$(document).ready(function() {	
	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true)	$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	$("#search").autocomplete(base_url + "executive/?o=getpidname", { width: 300, max: 50 });
	
	$("#add").click(doadd);
	
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});
});

function doadd(){
	var billtype = $('input:radio[name="billtype"]:checked').val();
	var nowpids = $("#pids").val();
	if(nowpids.indexOf("," + $("#search").val() + ",") == -1){
		$.ajax({
			type: "POST",
			url: "do.php",
			cache:"false",
			data: "action=search_invoice_executive&billtype=" + billtype + "&search=" + $("#search").val() + "&t=" + Math.random() + "&vcode=" + vcode,
			dataType:'text',
			async: false,
			success: function(msg){
				if(msg == "0"){
					alert("没有找到该执行单");
				}else if(msg == "1"){
					if(billtype == "1"){
						alert("该执行单的发票类型不是广告业，请重新选择");
					}else if(billtype == "2"){
						alert("该执行单的发票类型不是服务业，请重新选择");
					}
				}else{
					$("#pidinfo").append(msg);
					$("#pids").val(nowpids + $("#search").val() + ",");
					getallamount();
					$('input:radio[name="billtype"]').each(function(){
						$(this).attr("disabled",true);
					});
				}
			},
		 	error: function(e){
		 		alert("搜索关联执行单记录异常");
		 	}
		});
	}
}

function removepid(v,pid){
	$(v).parent().remove();
	var nowpids = $("#pids").val();
	$("#pids").val(nowpids.replace("," + pid + ",",","));
	getallamount();
	if($("#pids").val() == ","){
		$('input:radio[name="billtype"]').each(function(){
			$(this).attr("disabled",false);
		});
	}
}

function getallamount(){
	var amount=0;
	var t=0;
	var nowpids = $("#pids").val();
	nowpids = nowpids.split(",");
	for(var i=0;i<nowpids.length;i++){
		if(nowpids[i] != ""){
			var id = "#amount_" + nowpids[i];
			amount+=Number($(id).val());
		}
	}
	//$("#pidinfo").children().each(function(index, element) {
	//	amount+=Number($(this).find("#amount").val());
	//});
	$("#invoceamount").html(amount.toFixed(2));
}

function check_amount(obj,id){
	var newamount = Number(obj.value);
	var aid = "oldamount_" + id;
	var olamount = Number($("#" + aid).val()); 
	var spanid = "span_" + id;
	if(olamount >= newamount){
		$("#" + spanid).html("");
	}else{
		$("#" + spanid).html("<font color=\"red\">请注意：开票金额高于执行单金额</font>");
	}
}
function showzp(n){
	if (n == 2) {
		$("#showzp").show();
		$("#d1").addClass("validate[required,maxSize[200]]");
		$("#d2").addClass("validate[required,maxSize[200]]");
		$("#d3").addClass("validate[required,maxSize[200]]");
	}else{
		$("#showzp").hide();
		$("#d1").removeClass("validate[required,maxSize[200]]");
		$("#d2").removeClass("validate[required,maxSize[200]]");
		$("#d3").removeClass("validate[required,maxSize[200]]");
	}
}

</script>
</body>
</html>
