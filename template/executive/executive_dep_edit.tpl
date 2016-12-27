<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 执行单系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/pop.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/default/easyui.css"/>
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/icon.css"/>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">
		[TOP]
	</div>

	<div id="content" class="fix">
		<div class="crumbs">执行单管理系统</span></div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a>审核执行单-支持部门</a></li>
                <li><a>流转状态</a></li>
			</ul>
		</div>
        
        <div class="box">
			<div class="publicform fix">
				[PIDINFO]
				<br/>
				<form id="formID" method="post" action="[BASE_URL]executive/action.php" target="post_frame">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                    <tr class="gradeB">
                        <th><b><span style="cursor:hand;">审核意见与操作</span></b></th>
                    </tr>
                    <tr>
                        <td><input name="rejectstep" type="radio" value="0" checked="checked"/>驳回至发起人 &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                        <td><textarea style="width:400px;height:84px" id="remark" class="textarea" rows="3" name="remark"></textarea></td>
                    </tr>
                    <tr>
                    	<td><div class="btn_div"><input id="reject" type="button" value="审核驳回" class="btn_bh" /></div></td>
                    </tr>
                </table>
                
                <br />
                <table cellpadding="0" cellspacing="0" border="0" class="sbd1" width="100%">
                	<tr class="gradeB">
                        <th colspan="2" ><b>填写支持部门 [DEPNAME] 内容信息</b></th>
                    </tr>
                </table>
                <table cellpadding="0" cellspacing="0" border="0" class="sbd1" width="100%">
                	<tr bgcolor="#FCBAA7" style="display:[NONE]">
                        <td style="font-weight:bold">驳回理由：</td>
                        <td>[MSG]</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; width:150px;">参与人员：</td>
                        <td>
                        	<!--select id="actorlist" name="actorlist" class="validate[required,funcCall[check_actor]] select">[ACTORLIST]</select-->
                        	<select id="actorlist" name="actorlist" class="select">[ACTORLIST]</select>
                        	&nbsp;已选人员：<!--span id="actor_show">[ACTOR_SHOW]</span--><input id="actor" name="actor" type="text" class="validate[required] text_new" style="width:40%;" value="[DEP_ACTOR]" />
                        	<!--input id="actor" name="actor" type="hidden" class="text" style="width:40%;" value="[DEP_ACTOR]" /-->
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold">备注说明：</td>
                        <td><textarea id="edit_remark" name="edit_remark" class="textarea" style="width:40%;height:49px">[DEP_REMARK]</textarea></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold">附件上传</td>
                        <td>
                        	<div>
	                    		<input type="file" name="upfile" id="upfile" size="45"  style="height:20px;"/>&nbsp;
	                    		<input type="button" id="upload" value="上 传" onclick="up_uploadfile(this,'dids',0,0);" class="btn"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font><input type=hidden name="dids" id="dids" size="50" value="[DIDSVALUE]"/>
								[DEP_DIDS]
							</div>
                        </td>
                    </tr>
                     <!--tr>
	                	 <td style="font-weight:bold">执行单外包类型</td>
	                	 <td>
	                	  	<select name="outsourcing_type" class="select">
	                	  	<option value="">请选择执行单外包类型</option>
	                	  	[EXESOURCINGTYPESELECT]
	                	  	</select>
	                	 </td>
                	</tr-->
                    <tr>
                        <td style="font-weight:bold">成本明细：</td>
                        <td class="copydate">
                            <input type="button" id="addcost" value="添 加" class="btn"/> &nbsp;&nbsp;
                            总：<b><span style="color:#ff9933; font-size:14px" id="cost">[DEP_COST]</span></b>
                            [DEP_COSTINFO]
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold">成本支付明细：</td>
                        <td class="copydate">
                            <input type="button" id="addcostpayment" value="添 加" class="btn"/> &nbsp;&nbsp;<!--input type="button" id="addcybtn" value="&nbsp;拆月信息&nbsp;" class="longbtn"/-->
                            总：<b><span style="color:#ff9933; font-size:14px" id="costpayment">[DEP_COSTPAYMENT]</span></b> &nbsp;&nbsp;<font color="red"><b>*注意：如果原单成本没有拆月，请删除原来【所有】的成本支付明细条目重新添加后进行拆月</b></font>
                            [DEP_COSTPAYMENTINFO]
                        </td>
                    </tr>
                   <tr>
                	 <td style="font-weight:bold">执行成本拆月</td>
                	 <td>
                	 	<input type="button" id="addcybtn" value="拆 月" class="btn"/>
                	 </td>
                </tr>
                    <!--<tr>
                        <td style="font-weight:bold;">执行成本拆月：</td>
                        <td>
                            <input type="button" value="添加一条成本拆月信息" id="cy_addcost" /> &nbsp;
                            <b><span id="cyallcost" style="color:#0000FF; font-size:14px">0.00</span></b> 
                            <label>元</label>
                            <br />
                            <span id="cy_costinfolist"></span>
                        </td>
                    </tr>-->
                    <tr>
                        <td style="font-weight:bold">选择流程：</td>
                        <td><ul style="border:0;width:100%;" id="pcidlist">
                            [DEP_PROCESSLIST]
                            </ul>
                            <div style="clear:both;padding:4px;border:solid 1px #cecece;background:#efefef;"></div></td>
                    </tr>
                    <tr>
                    	<td colspan="2">
                    		<div class="btn_div">
                    			<input type="hidden" name="vcode" value="[VCODE]"/>
                    			<input type="hidden" name="pid" value="[PID]"/>
                    			<input type="hidden" name="dep" value="[DEP]"/>
                    			<input type="hidden" name="executive_id" value="[EXECUTIVEID]"/>
                				<input type="hidden" name="action" id="action" value="executive_dep_edit"/>
                				<input type="hidden" name="audit_pass" id="audit_pass" value="1"/>
                    			<input type="submit" value="提 交" class="btn_sub" name="sbtn"/>
                    		</div>
                    	</td>
                    </tr>
                </table>
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
<form id="cyform" method="post" action="[BASE_URL]executive/action.php"  target="post_frame">
<div class="overlay">
	[CYOVERLAY]
</div>
</form>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.autocomplete.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]executive/executive.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var actor_array = new Array();	//项目执行人员
var cy_on = [CYON];
var vcode = '[VCODE]';
$(document).ready(function() {
	$("#tab li").click(function(){
		$(this).addClass("on").siblings("li").removeClass();
		$(".box:eq("+$(this).index()+")").show().siblings(".box").hide();
	}).eq(0).click();
	
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true)	$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	//new
	$("#addcost").click(addcostinfo);
	
	$("#addcostpayment").click(addcostpayment);
	
	$("#actorlist").change(addactor);
	
	$("#reject").click(function(){  
		$("#remark").addClass("validate[required]");
		$("#actor").removeClass("validate[required]");
		if(window.confirm("您确定要审核驳回吗？")){
			$("#action").val("executive_audit");
			$("#audit_pass").val("0");
			$("#formID").submit();
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
	
	[CHECKDIFFERENT]

	if(cy_on){
		$("#addcybtn").click(addcy);
	}else{
		$("#addcybtn").hide();
	}
});

var suppliers = [SUPPLIERS];

function getSupplierName(obj){
	$(obj).autocomplete(base_url + "finance/supplier/?o=getSupplierName", { width: 300, max: 50 });
}

var supplier_categorys = '[SUPPLIERCATEGORYS]';
var supplier_shorts = '[SUPPLIERSHORTS]';
var supplier_industrys = '[SUPPLIERINDUSTRYS]';

function getindustry(){
	$('[id^=supplier_short_]').change(function(){
		var sid = $(this).attr("id");
		sid = sid.split("_");
		var val = $(this).val();

		var sis = $.parseJSON(supplier_industrys);
		var str = "<option value=\"\">请选择客户行业分类</option>";
		var req = "";
		for(var i=0;i<sis.length;i++){
			if(sis[i].short_id == val){
				var op =sis[i].industrys;
				req = "validate[required]";
				for(var ii=0;ii<op.length;ii++){
					str += "<option value=\"" + op[ii].id + "\">" + op[ii].name + "</option>";
				}
				break;
			}	
		}
		$("#industry_" + sid[2]).html(str);
		if(req == ""){
			$("#industry_" + sid[2]).removeClass("validate[required]");
		}else{
			$("#industry_" + sid[2]).addClass(req);
		}
	});
}
</script>
</body>
</html>
