<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 新建项目需求</title>
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
	<div class="nav_top">
		[TOP]
	</div>

	<div id="content" class="fix">
		<div class="crumbs">项目需求管理</div>
		<div class="tab">
			<ul>
				<li class="on"><a>新建项目需求</a></li>
				<li><a href="[BASE_URL]tec/?o=projectlist">项目需求列表</a></li>
			</ul>
		</div>
        
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]tec/action.php" target="post_frame">
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="font-weight:bold">关联执行单</td>
                    <td><input type="text" class="validate[optional,max[100]] text ac_input" style="width:300px;" id="pid" name="pid"/>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">项目名称</td>
                    <td id="td1"><input type="text" class="validate[required,maxSize[200]] text ac_input" style="width:300px;" id="project_name" name="project_name"/>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">项目分类</td>
                    <td>
                    	<select id="project_type" name="project_type" class="validate[required] select">
                        	<option value="1">客户项目</option>
                        	<option value="2">内部项目</option>
                        </select>
					</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">项目开发周期</td>
                    <td><textarea style="width:400px;height:80px" id="cycle" class="validate[required,maxSize[1000]] textarea" rows="3" name="cycle"></textarea>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">网站流量预估</td>
                    <td><textarea style="width:400px;height:80px" id="traffic" class="validate[optional,maxSize[1000]] textarea" rows="3" name="traffic"></textarea>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">附件上传</td>
                    <td>
                    	<div>
                    		<input type="file" name="upfile" id="upfile" size="45" class="text" style="height:20px;"/>&nbsp;
                    		<input type="button" id="upload" value="上传" onclick="up_uploadfile(this,'dids',0,0);" class="btn"/><input type="hidden" name="dids" id="dids" value="^"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font>
						</div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">项目背景</td>
                    <td>
                    	<textarea style="width:400px;height:80px" id="project_background" class="validate[required,maxSize[1000]] textarea" rows="3" name="project_background"></textarea>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">项目需求</td>
                    <td class="copydate">
                        <input type="button" value="添加" id="addprequirement" class="btn"/> &nbsp;&nbsp;
                        <input type="hidden" name="prequirement" id="prequirement" value=","/>
                        <br/>
                        <div id="prequirementlist"></div>
                    </td>
                </tr>
			</table>
			<div class="btn_div"><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="project_add"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
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
<!--script src="[BASE_URL]executive/executive.js"></script-->
<script src="[BASE_URL]tec/tec.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var prequirement_count = 1; //需求
$(document).ready(function() {
	$("#pid").autocomplete(base_url + "executive/index.php?o=getpidname_bytec", { width: 300, max: 50 });
	$("#pid").blur(function(){
		if($.trim($(this).val()) == ""){
			$("#project_name").val("");
		}else if($(this).val().indexOf("~")!=-1){
			var pn = $(this).val();
			$("#project_name").val(pn.substring(pn.indexOf("~")+1));
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
	
	$("#addprequirement").click(addprequirement);
});
</script>
</body>
</html>