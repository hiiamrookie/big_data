<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 客户合同管理系统</title>
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
		<div class="crumbs">客户合同管理系统</div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a>新建客户合同</a></li>
			</ul>
		</div>
                
        <div class="publicform fix">
        	<form id="formID" method="post" action="[BASE_URL]contract_cus/action.php" target="post_frame">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="width:190px; font-weight:bold">客户合同类型</td>
                    <td colspan="3">
                        <input type="radio" value="1" class="radio" name="type"  onclick="showtype(this)" checked="checked"  />
                        <label>框架 </label>
                        <input type="radio" value="2" class="radio" name="type" onclick="showtype(this)" />
                        <label>单笔</label>  
                        &nbsp;
                        <span id="showfmkcid" style="display:none">
                            <input type="checkbox" class="radio" name="isfmkcid" id="isfmkcid" />
                            <label>是否关联框架客户合同</label> 
                            
                            <select id="fmkcid" name="fmkcid" style="display:none" class="select">[FMKCIDLIST]</select>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold" >直客 / 代理商</td>
                    <td colspan="3">
                        <input type="radio" value="1" class="radio" name="type1" checked="checked"  />
                        <label>直客</label>
                        <input type="radio" value="2" class="radio" name="type1" />
                        <label>代理商</label>
                        <span id="showdaili" style="display:none"><input type="hidden" name="dailicount" id="dailicount" value=","/>                  
                            <br />
                            <input type="button" id="adddaili" value="&nbsp;添加代理商信息&nbsp;" class="longbtn"/><p><p/>
                            <span id="daililist"></span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">合同名称</td>
                    <td colspan="3"><input type="text" style="width:200px;height:20px;" name="name" id="name" class="validate[required,maxSize[200]]" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">客户名称</td>
                    <td colspan="3"><input type="text" style="width:200px;height:20px;" name="cusname" id="cusname" class="validate[optional,maxSize[200]]" value="" onblur="javascript:select_customer(this);"/></td>
                    <!--td style="width:190px; font-weight:bold">系统客户名称</td><td>[CUSTOMERSELECT]</td-->
                </tr>
                <tr>
                    <td style="font-weight:bold">客户联系方式</td>
                    <td colspan="3"><input type="text" style="width:300px;height:20px;" name="cuscontact" id="cuscontact" class="validate[optional,maxSize[500]]" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">约定执行确认方式</td>
                    <td colspan="3"><input type="text" style="width:200px;height:20px;" name="execution" id="execution" class="validate[optional,maxSize[200]]" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">签约部门</td>
                    <td colspan="3">
                    	<select id="city" name="city" class="validate[required] select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="validate[required] select"><option value="">请选择部门</option></select>&nbsp;<select id="team" name="team" class="select"><option value="">请选择团队</option></select>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">网迈联系人</td>
                    <td colspan="3">
                    	<select id="contactperson" name="contactperson" class="validate[required] select">
                        	<option value="">请选择</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">项目内容</td>
                    <td colspan="3"><input type="text" style="width:200px;height:20px;" name="contactcontent" id="contactcontent" class="validate[optional,maxSize[500]]" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">合同签订日期</td>
                    <td colspan="3"><input type="text" class="validate[required] text Wdate" name="signdate" id="signdate" style="width:100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">合同执行日期</td>
                    <td colspan="3">
                        起始
                        <input type="text" class="validate[required] text Wdate" name="starttime" id="starttime" style="width:100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" value="" /> 
                        &nbsp;
                        终止
                        <input type="text" class="validate[required] text Wdate" name="endtime" id="endtime" style="width:100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" value="" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">监测系统</td>
                    <td colspan="3"><input type="text" class="validate[optional,maxSize[500]]" name="monitoringsystem" id="monitoringsystem" style="width:200px;height:20px;" /></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">合同金额</td>
                    <td colspan="3">
                        <input type="text" class="validate[required,custom[number]]" style="width:100px; text-align:right;height:20px;" name="contractamount" id="contractamount" value="" />
                        <label>元</label>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">开票类型</td>
                    <td colspan="3">
                        <select id="billtype" name="billtype" class="select">
                        	<option value="1">广告</option>
                            <option value="2">服务</option>
                        </select> 
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">合同金额拆分</td>
                    <td colspan="3">
                    	<input type="button" value="&nbsp;添加媒介投放内容&nbsp;" id="addcf1" class="longbtn"/><br /><input type="hidden" name="mediatfcount" id="mediatfcount" value=","/>
                        <div id="cflist1"></div>
                        <br />
                        <input type="button" value="&nbsp;添加服务内容&nbsp;" id="addcf2" class="longbtn"/><br /><input type="hidden" name="servicecount" id="servicecount" value=","/>
                        <div id="cflist2"></div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">返点比例</td>
                    <td colspan="3"><input type="text" style="width:20px;height:20px;" name="rebateproportion" id="rebateproportion" class="validate[optional,custom[number],min[0],max[100]]"/> %</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">保证金支付</td>
                    <td colspan="3">
                    	<input type="button" value="添 加" id="addbzj" class="btn"/><br /><input type="hidden" name="baozhengjincount" id="baozhengjincount" value=","/>
                    	<div id="bzjlist"></div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">保证金支付方式</td>
                    <td colspan="3"><textarea style="width:400px; height:100px" name="bzjpaymentmethod" id="bzjpaymentmethod" class="text" ></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">合同金额支付方式及付款日期</td>
                    <td colspan="3"><textarea style="width:400px; height:100px" name="contractamountpayment" id="contractamountpayment" class="text" ></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">特殊违约条款</td>
                    <td colspan="3"><textarea style="width:400px; height:100px" name="specialcaluse" id="specialcaluse" class="text" ></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">备注</td>
                    <td colspan="3"><textarea style="width:400px; height:100px" name="remark" id="remark" class="text" ></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">附件上传</td>
                    <td colspan="3">
                    	<div>
                    		<input type="file" name="upfile" id="upfile" size="45" style="height:20px;height:20px;"/>&nbsp;
                    		<input type="button" id="upload" value="上 传" onclick="up_uploadfile(this,'dids',0,0);" class="btn"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font><input type="hidden" name="dids" id="dids" size="50" value="^"/>
						</div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">合同状态</td>
                    <td colspan="3">
                        <input type="radio" name="contractstatus" value="1" onclick="showfilestatus(this)" /><label>已归档</label>
                        <input type="radio" name="contractstatus" value="2" onclick="showfilestatus(this)" checked="checked" /><label>未归档</label>
                        &nbsp;
                        <span id="showfilereason" >理由&nbsp;&nbsp;<input type="text" style="height:20px;width:300" class="validate[optional,maxSize[500]]"  name="contractstatusreason" id="contractstatusreason" /></span>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">选择应用流程</td>
                    <td colspan="3">
                        <ul style="border:0;width:100%;" id="pcidlist">[PROCESSLIST]</ul>
                        <div style="clear:both;padding:4px;border:solid 1px #cecece;background:#efefef;"></div>
                    </td>
                </tr>
            </table>
            <div class="btn_div"><input type="hidden" name="action" value="contract_add"/><input type="hidden" name="vcode" value="[VCODE]"/><input type="submit" id="submit" value="提 交" class="btn_sub" /></div>
            </form>
            <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
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
var vcode = "[VCODE]";
var base_url = '[BASE_URL]';
var daili_count = 1;
var mediatf_count = 1;
var service_count = 1;
var baozhengjin_count = 1;
var fw_amount_options = '[FWAMOUNTOPTIONS]';
$(document).ready(function() {
	$("#pcidlist input[type=radio]").live("click",function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});

	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true){
			$("#pcidlist").next("div").show().html($(this).next('span').text());
		}
	});
	
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
