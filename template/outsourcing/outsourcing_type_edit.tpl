<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 编辑执行单外包类型</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/pop.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">
		[TOP]
	</div>

	<div id="content" class="fix">
		<div class="crumbs">外包流程管理</div>
		<div class="tab">
			<ul>
				<li class="on"><a>编辑执行单外包类型</a></li>
				<li><a href="[BASE_URL]outsourcing/?o=typelist">执行单外包类型列表</a></li>
			</ul>
		</div>
        
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]outsourcing/action.php" target="post_frame">
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="font-weight:bold">外包类型名称</td>
                    <td><input type="text" name="outsourcing_typename" id="outsourcing_typename" style="width:200px;" class="validate[required,maxSize[255]] text_new" value="[OUTSOURCINGTYPENAME]"/></td>
                </tr>
                 <tr>
                	<td style="font-weight:bold">最大金额限制</td>
                	<td><input type="checkbox" name="is_max_amount" id="is_max_amount" value="1" [ISMAXAMOUNT]/>&nbsp;无限制&nbsp;&nbsp;&nbsp;<input type="text" name="max_amount" id="max_amount" class="validate[optional,custom[money],min[0.01]] text_new" value="[MAXAMOUNT]" [MAXAMOUNTREADONLY]/>&nbsp;元</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">备注</td>
                    <td><textarea style="width:400px;height:80px" id="remark" class="validate[optional,maxSize[500]] textarea" rows="3" name="remark">[REMARK]</textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">关联外包审核流程</td>
                    <td><select id="outsourcing_process" name="outsourcing_process" class="select">
                    		[OUTSOURCINGPROCESS]
                        </select>&nbsp;&nbsp;预览：<span id="process_view"></span></td>
                </tr>
			</table>
			<div class="btn_div"><input type="hidden" name="id" value="[ID]"/><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="update_outsoourcing_type"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
			</div>
			</form>
			<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
	</div>
</div>
<script src="[BASE_URL]script/jquery.min.js"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script src="[BASE_URL]script/ajaxfileupload.js"></script>
<script src="[BASE_URL]script/My97DatePicker/WdatePicker.js"></script>
<script src="[BASE_URL]script/jquery.autocomplete.min.js"></script>
<script src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script src="[BASE_URL]js/nimads.js"></script>
<script src="[BASE_URL]js/js.js"></script>
<script src="[BASE_URL]js/upload.js"></script>
<script src="[BASE_URL]outsourcing/outsourcing.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var vcode = '[VCODE]';
var base_url = '[BASE_URL]';

$(document).ready(function() {
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true){
			$("#pcidlist").next("div").show().html($(this).next('span').text());
		}
	});

	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

	$("#is_max_amount").click(function(){
		if($(this).attr("checked")=="checked"){
			$("#max_amount").val("");
			$("#max_amount").attr("readonly",true);
		}else{
			$("#max_amount").attr("readonly",false);
		}
	});

	$("#outsourcing_process").change(function(){
		if($(this).val()==""){
			$("#process_view").html("");
		}else{
			if($(this).val() != ""){	
				$.ajax({
					type: "POST",
					url: base_url + "outsourcing/do.php",
					cache:"false",
					data: "action=getOutsourcingProcessView&id=" + $(this).val() + "&t=" + Math.random() + "&vcode=" + vcode,
					dataType:'text',
					async: false,
					success: function(msg){
						$("#process_view").html(msg);
					},
					 error: function(e){
					 	alert("预览执行单外包审核流程出错");
					 }
				});
			}
		}
	});
});
</script>
</body>
</html>
