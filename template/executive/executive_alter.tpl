<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 变更执行单</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]css/pop.css" rel="stylesheet" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
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
		<div class="crumbs">执行单管理系统</div>
		<div class="tab" id="tab">
			<ul>
				<li class="on"><a>变更执行单</a></li>
                <li><a>流转状态</a></li>
			</ul>
		</div>
        <div class="box">
            <div class="publicform fix">
            	<form id="formID" method="post" action="[BASE_URL]executive/action.php" target="post_frame">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                    <tr>
                        <td style="font-weight:bold" colspan="2">执行单号</td>
                        <td><b>[PID]</b><input type="hidden" name="pid" value="[PID]"/></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" width="150" colspan="2">所属地区/部门/团队</td>
                        <td>
                            [CITYINFO]
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">执行单类型</td>
                        <td><input type="radio" value="1" id="type" name="exetype" [TYPE1]/>
                            普通 &nbsp;
                            <input type="radio" value="2" id="type" name="exetype" [TYPE2]/>
                            预充值 &nbsp;
                            <input type="radio" value="3" id="type" name="exetype" [TYPE3]/>
                            结算</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">所属公司</td>
                        <td>
                            <input type="radio" name="execompany" class="radio" value="3" [COMPANY3]/>
                            <label>新网迈</label>
                            <input type="radio" name="execompany" class="radio" value="1" [COMPANY1]/>
                            <label>网迈广告</label>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">项目名称</td>
                        <td>
                            <input type="text" name="projectname" id="projectname" style="width:300px;height:20px;" class="validate[required,maxSize[200]]" value="[NAME]" />
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">附件上传</td>
                        <td>
                            <div>
                    			<input type="file" name="upfile" id="upfile" size="45" style="height:20px;height:20px;"/>&nbsp;
                    			<input type="button" id="upload" value="上 传" onclick="up_uploadfile(this,'dids',0,0);" class="btn"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font><input type="hidden" name="dids" id="dids" size="50" value="[DIDSVALUE]"/>
							</div>
							[DIDS]
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">项目负责人</td>
                        <td>
                            <select id="principal" name="principal" class="validate[required] select">
                                [PRINCIPAL]
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">项目执行人员</td>
                        <td>
                            <!--select id="actorlist" name="actorlist" class="validate[required,funcCall[check_actor]] select"-->
                            <select id="actorlist" name="actorlist" class="select">
                                [ACTORLIST]
                            </select>
                            已选人员：<!--span id="actor_show">[ACTOR_SHOW]</span--><input type="text" style="width:300px;" class="validate[required] text_new" id="actor" name="actor" value="[ACTOR]"/>
                            <!--input type="hidden" style="width:300px;" class="text ac_input" id="actor" name="actor" value="[ACTOR]"/-->
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">项目实际执行期</td>
                        <td>开始
                            <input type="text" onclick="checkdatetype();" style="width:100px" id="starttime" name="starttime" class="validate[required] text Wdate" value="[STARTTIME]" />
                            &nbsp;&nbsp;
                            结束
                            <input type="text" onclick="WdatePicker({minDate:'#F{$dp.$D(\'starttime\')}'})" style="width:100px" id="endtime" name="endtime" class="validate[required] text Wdate" value="[ENDTIME]" /></td>
                    </tr>
                     <tr><td colspan="3">&nbsp;</td></tr>
                    <tr>
                    	<td rowspan="3" style="font-weight:bold;width:10px;">执<br/>行<br/>金<br/>额</td>
                        <td style="font-weight:bold">合同约定付款日期</td>
                        <td class="copydate">
                       	 	<input type="button" value="添 加" id="addpaytime" class="btn"/> &nbsp;&nbsp;
                        	付款总金额 <b><span style="color:#ff9933; font-size:14px" id="amount">[AMOUNT]</span></b> 元
                        	[PAYTIMEINFO] 
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold">税前、税费金额拆分</td>
                        <td class="copydate">
                        	<input type="button" value="添 加" id="addservicecf" class="btn"/> &nbsp;&nbsp;
                        	税前合计 <b><span style="color:#ff9933; font-size:14px" id="allservicecfamount">[ALLSERVICECFAMOUNT]</span></b> 元&nbsp;&nbsp;税费(<font color="#0033CC"><span id="taxrate">[TAXRATESHOW]%</span></font>)
                            <b><span style="color:#03C; font-size:14px" id="taxamount">[TAXMOUNT]</span></b> 元
                            [SERVICECFLIST]
                        </td>
                    </tr>
                     <tr>
                	 <td style="font-weight:bold">执行金额拆月</td>
	                	 <td>
	                	 	<input type="button" id="addamountcybtn" value="拆 月" class="btn"/> &nbsp;&nbsp;
	                	 	小计 <b><span style="color:#ff9933; font-size:14px" id="amountcy">[CYAMOUNT]</span></b> 元
	                	 	<!--input type="hidden" name="cy_amount" id="cy_amount" value=","/>
	                        <div id="cyamountlist"></div-->
	                        [CYAMOUNTLIST]
	                	 </td>
               		 </tr>
               		 <tr><td colspan="3">&nbsp;</td></tr>
                    <tr>
                    	<td rowspan="3" style="font-weight:bold;width:10px;">执<br/>行<br/>成<br/>本</td>
                        <td style="font-weight:bold">成本明细</td>
                        <td class="copydate">
                        	<input type="button" id="addcost" value="添 加" class="btn"/> &nbsp;&nbsp;
                        	成本明细总额 <b><span style="color:#ff9933; font-size:14px" id="cost">[COST]</span></b> 元	
                            [COSTINFO]
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold">成本支付明细</td>
                        <td class="copydate">
                        	<input type="button" id="addcostpayment" value="添 加" class="btn"/> &nbsp;&nbsp;
                        	成本明细支付总额 <b><span style="color:#ff9933; font-size:14px" id="costpayment">[COSTPAYMENT]</span></b> 元	&nbsp;&nbsp;<font color="red"><b>*注意：如果原单成本没有拆月，请删除原来【所有】的成本支付明细条目重新添加后进行拆月</b></font>
                            [COSTPAYMENTINFO]
                        </td>
                    </tr>          
                    <tr>
                	 <td style="font-weight:bold">执行成本拆月</td>
                	 <td>
                	 	<input type="button" id="addcybtn" value="拆 月" class="btn"/><input type="hidden" name="suppliers" id="suppliers"/>
                	 </td>
                </tr>  
                 <tr><td colspan="3">&nbsp;</td></tr>        
                    <tr>
                        <td style="font-weight:bold" colspan="2">备注</td>
                        <td><textarea style="width:400px;height:80px" id="remark" class="validate[optional,maxSize[1000]] textarea" rows="3" name="remark">[REMARK]</textarea></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">可变更支持部门</td>
                        <td><ul style="background:none; border:0;width:69%;">[ALTERSUPPORTDEP]</ul></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">可增加支持部门</td>
                        <td><ul style="background:none; border:0;width:69%;">[SUPPORTDEP]</ul></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold" colspan="2">选择应用流程</td>
                        <td>
                            <ul style="border:0;width:100%;" id="pcidlist">
                            [PROCESSLIST]
                            </ul>
                            <div style="clear:both;padding:4px;border:solid 1px #cecece;background:#efefef;"></div>
                        </td>
                    </tr>
                </table>
                <div class="btn_div"><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action"  value="executive_alter"/><input type="submit" value="提 交" class="btn_sub" id="submit" /></div>
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
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.autocomplete.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]executive/executive.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
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

	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});
	
	$("#addpaytime").click(addpaytime);
	$("#addcost").click(addcostinfo);
	$("#addcostpayment").click(addcostpayment);
	$("#actorlist").change(addactor);
	$("#addservicecf").click(addservicecf);

	if(cy_on){
		$("#addcybtn").click(addcy);
		$("#addamountcybtn").click(addamountcy);
	}else{
		$("#addcybtn").hide();
		$("#addamountcybtn").hide();
	}

	$("#starttime").blur(function(){
		$("#hiddenstarttime").val($("#starttime").val());
	});

	$("#endtime").blur(function(){
		$("#hiddenendtime").val($("#endtime").val());
	});
});

var suppliers = [SUPPLIERS];

function getSupplierName(obj){
	$(obj).autocomplete(base_url + "finance/supplier/?o=getSupplierName", { width: 300, max: 50 });
}

function setCYDateRange(obj){
	var id = obj.id;
	var hiddenid = "hidden" + id;
	var val = obj.value;
	$("#" + hiddenid).val(obj.value);
}

var supplier_categorys = '[SUPPLIERCATEGORYS]';
var supplier_shorts = '[SUPPLIERSHORTS]';
var supplier_industrys = '[SUPPLIERINDUSTRYS]';

var tax_rate_select = '[TAXRATESELECT]';

//718 add
var tax_rate_718 = [TAXRATE];

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