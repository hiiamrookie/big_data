<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 新建执行单</title>
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
		<div class="crumbs">执行单管理系统</div>
		<div class="tab">
			<ul>
				<li class="on"><a>新建执行单</a></li>
			</ul>
		</div>
        
		<div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]executive/action.php" target="post_frame">
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
            	<tr>
                    <td style="font-weight:bold;width:150px;" colspan="2">所属地区/部门/团队</td>
                    <td>
                    	[CITYINFO]
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold" colspan="2">执行单类型</td>
                    <td><input type="radio" checked="checked" value="1" id="type" name="exetype"/>
                        普通 &nbsp;
                        <input type="radio" value="2" id="type" name="exetype"/>
                        预充值 &nbsp;
                        <input type="radio" value="3" id="type" name="exetype"/>
                        结算</td>
                </tr>
                <tr>
                    <td style="font-weight:bold" colspan="2">所属合同</td>
                    <td><input type="text" class="validate[required] ac_input text_new" style="width:300px;" id="cid" name="cid"/>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold" colspan="2">所属公司</td>
                    <td>
                        <input type="radio" name="execompany" class="radio" value="3" checked="checked" />
                        <label>新网迈</label>
                        <input type="radio" name="execompany" class="radio" value="1"/>
                        <label>网迈广告</label>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold" colspan="2">项目名称</td>
                    <td><input type="text" name="projectname" id="projectname" style="width:200px;" class="validate[required,maxSize[200]] text_new"/></td>
                </tr>
                <tr>
                    <td style="font-weight:bold" colspan="2">附件上传</td>
                    <td>
                    	<div>
                    		<input type="file" name="upfile" id="upfile" size="40" style="height:20px;"/>&nbsp;
                    		<input type="button" id="upload" value="上 传" onclick="up_uploadfile(this,'dids',0,0);" class="btn"/><input type="hidden" name="dids" id="dids" size="50" value="^"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font>
						</div>
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
                        	[ACTOR]
                        </select>
                        已选人员：<!--span id="actor_show"></span--> <input type="text" style="width:300px;" class="validate[required] text_new" id="actor" name="actor"/>
                        <!--input type="hidden" style="width:300px;" class="text ac_input" id="actor" name="actor"/-->
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold" colspan="2">项目实际执行期</td>
                    <td>开始
                        <input type="text" onclick="checkdatetype();" style="width:100px" id="starttime" name="starttime" class="validate[required] text Wdate" readonly="readonly" onblur="javascript:setCYDateRange(this);"/>
                        &nbsp;&nbsp;
                        结束
                        <input type="text" onclick="WdatePicker({minDate:'#F{$dp.$D(\'starttime\')}'})" style="width:100px" id="endtime" name="endtime" class="validate[required] text Wdate" readonly="readonly"  onblur="javascript:setCYDateRange(this);"/>
                    </td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                	<td rowspan="3" style="font-weight:bold;width:10px;">执<br/>行<br/>金<br/>额</td>
                    <td style="font-weight:bold">合同约定付款日期</td>
                    <td class="copydate">
                    	<input type="button" value="添 加" id="addpaytime" class="btn"/> &nbsp;&nbsp;
                    	付款总金额 <b><span style="color:#ff9933; font-size:14px" id="amount">0.00</span></b> 元<br/>
                        <input type="hidden" name="paycount" id="paycount" value=","/>
                        <div id="paytimelist"></div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">税前、税费金额拆分</td>
                    <td class="copydate">
                       	 <input type="button" value="添 加" id="addservicecf" class="btn"/> &nbsp;&nbsp;
                       	 税前合计 <b><span style="color:#ff9933; font-size:14px" id="allservicecfamount">0.00</span></b> 元&nbsp;&nbsp;税费(<font color="#0033CC"><span id="taxrate">6.83%</span></font>)
                        <b><span style="color:#03C; font-size:14px" id="taxamount">0.00</span></b> 元<br/>
                        <input type="hidden" name="servicecf" id="servicecf" value=","/>
                        <div id="servicecflist"></div>
                    </td>
                </tr>
                <tr>
                	 <td style="font-weight:bold">执行金额拆月</td>
                	 <td>
                	 	<input type="button" id="addamountcybtn" value="拆 月" class="btn"/> &nbsp;&nbsp;
                	 	小计 <b><span style="color:#ff9933; font-size:14px" id="amountcy">0.00</span></b> 元<br/>
                	 	<input type="hidden" name="cy_amount" id="cy_amount" value=","/>
                        <div id="cyamountlist"></div>
                	 </td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                	<td rowspan="3" style="font-weight:bold;width:10px;">执<br/>行<br/>成<br/>本</td>
                    <td style="font-weight:bold" >成本明细</td>
                    <td class="copydate">
                    	<input type="button" id="addcost" value="添 加" class="btn"/> &nbsp;&nbsp;
                    	成本明细总额 <b><span style="color:#ff9933; font-size:14px" id="cost">0.00</span></b> 元<br/>
                        <input type="hidden" name="costcount" id="costcount" value=","/>
                        <div id="costinfolist"></div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold">成本支付明细</td>
                    <td class="copydate">
                    	<input type="button" id="addcostpayment" value="添 加" class="btn"/> &nbsp;&nbsp;
                    	 成本明细支付总额 <b><span style="color:#ff9933; font-size:14px" id="costpayment">0.00</span></b> 元<br />
                        <input type="hidden" name="costpaycount" id="costpaycount" value=","/>
                        <div id="costpaymentinfolist"></div>
                    </td>
                </tr>
                <tr>
                	 <td style="font-weight:bold">执行成本拆月</td>
                	 <td>
                	 	<input type="button" id="addcybtn" value="拆 月" class="btn"/><input type="hidden" name="suppliers" id="suppliers"/><input type="hidden" name="cy_json" id="cy_json"/>
                	 </td>
                </tr>
                <!--<tr>
                    <td style="font-weight:bold;">执行金额拆月</td>
                    <td>
                    	<input type="button" value="添加一条金额拆月信息" id="cy_addamount" /> &nbsp;
                        <b><span style="color:#0000FF; font-size:14px" id="cyallamount" >0.00</span></b> 
                        <label>元</label>
                        <br />
                        <span id="cy_amountinfolist"></span>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">执行成本拆月</td>
                    <td>
                        <input type="button" value="添加一条成本拆月信息" id="cy_addcost" /> &nbsp;
                        <b><span id="cyallcost" style="color:#0000FF; font-size:14px">0.00</span></b> 
                        <label>元</label>
                        <br />
                        <span id="cy_costinfolist"></span>
                    </td>
                </tr>      -->
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                    <td style="font-weight:bold" colspan="2">备注</td>
                    <td><textarea style="width:400px;height:80px" id="remark" class="validate[optional,maxSize[1000]] textarea" rows="3" name="remark"></textarea></td>
                </tr>
                <tr>
                    <td style="font-weight:bold" colspan="2">支持部门（选填）</td>
                    <td>[SUPPORTDEP]</td>
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
			<div class="btn_div"><input type="hidden" name="token"  id="token" value="[TOKEN]"/><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="executive_add"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
			</div>
			</form>
			<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
	</div>
</div>
<form id="cyform" method="post" action="[BASE_URL]executive/action.php"  target="post_frame">
<div class="overlay">
	<div class="scbox">
	</div>
	<input type="hidden" name="isaltershow" value="0"/><input type="hidden" name="hiddenstarttime" id="hiddenstarttime"/><input type="hidden" name="hiddenendtime" id="hiddenendtime"/><input type="hidden" name="copycostpaycount" id="copycostpaycount"/><input type="hidden" name="action" value="executive_cy"/><input type="hidden" name="vcode" value="[VCODE]"/><img src="[BASE_URL]images/none.gif" class="close" onclick="close_pop();"/>
</div>
</form>
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
<script src="[BASE_URL]executive/executive.js"></script>
<script src="[BASE_URL]js/common.js"></script>
<script type="text/javascript">
var vcode = '[VCODE]';
var base_url = '[BASE_URL]';
var pay_count = 1;	//合同约定付款
var cost_count = 1;	//成本明细
var cost_pay_count = 1; //成本支付明细;
var service_cf_count = 1; //服务金额拆分
var actor_array = new Array();	//项目执行人员
var customer_safety_on = [CUSTOMERSAFETYON];
var cy_on = [CYON];
var suppliers = new Array();	
var cy_amount_count = 1;//执行金额拆月


$(document).ready(function() {
	$("#pcidlist input[type=radio]").click(function(){
		$("#pcidlist").next("div").show().html($(this).next('span').text());
	});
	
	$("#pcidlist input[type=radio]").each(function(){
		if (this.checked==true){
			$("#pcidlist").next("div").show().html($(this).next('span').text());
		}
	});
	
	$("#cid").autocomplete(base_url + "contract_cus/index.php?o=getcid", { width: 300, max: 50 });
	
	$("#cid").blur(function(){
		var ci = $(this).val();
		if(ci.indexOf("-") != -1){
			ci = ci.split("-");
			$.ajax({
				   type: "POST",
				   url: "do.php",
				   cache:false,
				   data: "action=getTaxRate&cid=" + ci[0] + "&t=" + Math.random() + "&vcode=" + vcode,
				   dataType:'text',
				   async: false,
				   success: function(msg){
				   		if(tax_rate_718 != msg && tax_rate_718 != 0 && $("#servicecf").val() != ","){
				   			alert("所属合同已变，请重新将税前、税费金额拆分");
				   			$("#servicecflist").html("");
				   			$("#servicecf").val(",");
				   			$("#allservicecfamount").html("0.00");
				   			$("#taxamount").html("0.00");
				   		}
					    tax_rate_718 = msg;
				   },
			 	   error: function(e){
			 		   alert("获取税点信息出错");
			 		   return;
			 	   }
			});
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
	
	//new
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
	
	if(customer_safety_on){
		$("#cid").blur(function(){
			if($(this).val() != ""){
				$.ajax({
					   type: "POST",
					   url: "do.php",
					   cache:false,
					   data: "action=check_customer&cid=" + $(this).val() + "&t=" + Math.random() + "&vcode=" + vcode,
					   dataType:'text',
					   async: false,
					   success: function(msg){
						   if(msg !="1"){
							   alert(msg);
							   $("#cid").val("");
						  }
					   },
				 	   error: function(e){
				 		   alert("检查系统客户保险余额出错");
				 	   }
				});
			}
		});
	}
});

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
var tax_rate_718 = 0;

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
