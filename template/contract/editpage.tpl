<table cellpadding="0" cellspacing="0" border="0" class="sbd1" style="width: 100%; min-width: 950">
	<tr>
		<td style="width:190px; font-weight:bold">立项</td>
        <td colspan="3">
        	<select id="project_id" name="project_id" class="validate[required] select">
            	[PROJECTSELECT]
            </select>
        	</td>
        </tr>
	<tr>
		<td style="font-weight: bold; width: 190px;">合同单号</td>
		<td style="font-size: 14px"  colspan="3"><b>[CID]</b></td>
	</tr>
	<tr>
		<td style="font-weight: bold">客户合同类型</td>
		<td  colspan="3">
			<input type="radio" value="1" class="radio" name="type" id="type" onclick="showtype(this)" [TYPE1_1]/> 
			<label>框架 </label> 
			<input type="radio" value="2" class="radio" name="type" id="type" onclick="showtype(this)" [TYPE1_2]/> 
			<label>单笔</label>
			 &nbsp; 
			 <span id="showfmkcid" [SHOWFMKCID]> 
			 	<input type="checkbox" class="radio" name="isfmkcid" id="isfmkcid" [ISFMKCID] /> 
			 	<label>是否关联框架客户合同</label> 
			 	<select id="fmkcid" name="fmkcid" [FMKCID] class="select">
					[FMKCIDLIST]
				</select> 
			</span>
		</td>
	</tr>
	<tr>
		<td style="font-weight: bold">直客 / 代理商</td>
		<td  colspan="3">
			<input type="radio" value="1" class="radio" name="type1" id="type1" [TYPE2_1] /> 
			<label>直客</label> 
			<input type="radio" value="2" class="radio" name="type1" id="type1" [TYPE2_2] /> 
			<label>代理商</label> 
			<span id="showdaili" [SHOWDAILIINFO]> <input type="hidden" name="dailicount" id="dailicount" value="[DAILICOUNT]"/>
				<br />
				<input type="button" id="adddaili" value="&nbsp;添加代理商信息&nbsp;" class="longbtn"/> <p></p>
				<span id="daililist">[DAILIINFO]</span> 
			</span>
		</td>
	</tr>
	<tr>
		<td style="font-weight: bold">合同名称</td>
		<td colspan="3"><input type="text" style="width: 200px;height:20px;" id="name" name="name" class="validate[required,maxSize[200]]" value="[CONTRACTNAME]" /></td>
	</tr>
	<tr>
		<td style="font-weight: bold">客户名称</td>
		<td colspan="3"><input type="text" style="width: 200px;height:20px;" name="cusname" id="cusname" class="validate[optional,maxSize[200]]" value="[CUSNAME]" onblur="javascript:select_customer(this);"/></td>
		<!--td style="width:190px; font-weight:bold">系统客户名称</td>
		<td>[CUSTOMERSELECT]</td-->
	</tr>
	<tr>
		<td style="font-weight: bold">客户联系方式</td>
		<td colspan="3"><input type="text" style="width: 300px;height:20px;" name="cuscontact" id="cuscontact" class="validate[optional,maxSize[500]]" value="[CUSCONTACT]" /></td>
	</tr>
	<tr>
		<td style="font-weight: bold">约定执行确认方式</td>
		<td colspan="3"><input type="text" style="width: 200px;height:20px;" name="execution" id="execution" class="validate[optional,maxSize[200]]" value="[EXECUTION]" /></td>
	</tr>
	<tr>
		<td style="font-weight: bold">签约部门</td>
		<td colspan="3">
			<select id="city" name="city" class="validate[required] select">[CITYS]</select>&nbsp;<select id="dep" name="dep" class="validate[required] select">[DEPS]</select>&nbsp;<select id="team" name="team" class="select">[TEAMS]</select>
		</td>
	</tr>
	<tr>
		<td style="font-weight: bold">联系人</td>
		<td colspan="3"><select name="contactperson" id="contactperson" class="validate[required] select">
			[CONTACTPERSON]
		</select>
	</tr>
	<tr>
		<td style="font-weight: bold">项目内容</td>
		<td colspan="3"><input type="text" style="width: 200px;height:20px;" name="contactcontent" id="contactcontent" class="validate[optional,maxSize[500]]" value="[CONTRACTCONTENT]" /></td>
	</tr>
	<tr>
		<td style="font-weight: bold">合同签订日期</td>
		<td colspan="3"><input type="text" class="validate[required] text Wdate" name="signdate" id="signdate" style="width: 100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" value="[SIGNDATE]" /></td>
	</tr>
	<tr>
		<td style="font-weight: bold">合同执行日期</td>
		<td colspan="3">起始 <input type="text" class="validate[required] text Wdate" name="starttime" id="starttime" style="width: 100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" value="[STARTTIME]" /> &nbsp; 终止 <input type="text" class="validate[required] text Wdate" name="endtime" id="endtime" style="width: 100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" value="[ENDTIME]" /></td>
	</tr>
	<tr>
		<td style="font-weight: bold">监测系统</td>
		<td colspan="3"><input type="text" class="validate[optional,maxSize[500]]" name="monitoringsystem" id="monitoringsystem" style="width: 200px;height:20px;" value="[MONITIORINGSYSTEM]" /></td>
	</tr>
	<tr>
		<td style="font-weight: bold">合同金额</td>
		<td colspan="3"><input type="text" class="validate[required,custom[number]]" style="width: 100px; text-align: right;height:20px;" name="contractamount" id="contractamount" value="[CONTRACTAMOUNT]" /> <label>元</label></td>
	</tr>
	<tr>
		<td style="font-weight: bold">开票类型</td>
		<td colspan="3"><select id="billtype" name="billtype" class="select">
			<option value="1" [SELECT_1]>广告</option>
			<option value="2" [SELECT_2]>服务</option>
		</select></td>
	</tr>
	<tr>
		<td style="font-weight: bold">合同金额拆分</td>
		<td colspan="3"><input type="button" value="&nbsp;添加媒介投放内容&nbsp;" id="addcf1" class="longbtn"/><input type="hidden" name="mediatfcount" id="mediatfcount" value="[MEDIATFCOUNT]"/><br />
		<div id="cflist1">[MTINFO]</div> <br />
		<input type="button" value="&nbsp;添加服务内容&nbsp;" id="addcf2" class="longbtn"/><input type="hidden" name="servicecount" id="servicecount" value="[SERVICECOUNT]"/><br />
		<div id="cflist2">[FWINFO]</div></td>
	</tr>
	<tr>
		<td style="font-weight: bold">返点比例</td>
		<td colspan="3"><input type="text" style="width: 20px;height:20px;" name="rebateproportion" id="rebateproportion" class="validate[optional,custom[number],min[0],max[100]]" value="[REBATEPROPORTION]" /> %</td>
	</tr>
	<tr>
		<td style="font-weight: bold">保证金支付</td>
		<td colspan="3"><input type="button" value="添 加" id="addbzj" class="btn"/><input type="hidden" name="baozhengjincount" id="baozhengjincount" value="[BAOZHENGJINCOUNT]"/><br />
		<div id="bzjlist">[BZJINFO]</div></td>
	</tr>
	<tr>
		<td style="font-weight: bold">保证金支付方式</td>
		<td colspan="3"><textarea style="width: 400px; height: 100px" name="bzjpaymentmethod" id="bzjpaymentmethod" class="text">[BZJPAYMENTMETHOD]</textarea></td>
	</tr>
	<tr>
		<td style="font-weight: bold">合同金额支付方式及付款日期</td>
		<td colspan="3"><textarea style="width: 400px; height: 100px" name="contractamountpayment" id="contractamountpayment" class="text">[CONTRACTAMOUNTPAYMENT]</textarea></td>
	</tr>
	<tr>
		<td style="font-weight: bold">特殊违约条款</td>
		<td colspan="3"><textarea style="width: 400px; height: 100px" name="specialcaluse" id="specialcaluse" class="text">[SPECIALCALUSE]</textarea></td>
	</tr>
	<tr>
		<td style="font-weight: bold">备注</td>
		<td colspan="3"><textarea style="width: 400px; height: 100px" name="remark" id="remark" class="text">[REMARK]</textarea></td>
	</tr>
	<tr>
		<td style="font-weight: bold">附件上传</td>
		<td colspan="3">
			<div>
	        	<input type="file" name="upfile" id="upfile" size="45" style="height:20px;"/>&nbsp;
	            <input type="button" id="upload" value="上 传" onclick="up_uploadfile(this,'dids',0,0);" class="btn"/>&nbsp;&nbsp;<font color="red">*只能上传 [VALIDATE_TYPE] 类型的文件，且单个文件最多 [VALIDATE_SIZE]M</font><input type="hidden" name="dids" id="dids" size="50" value="[DIDS]"/>
			</div>
		 [UPLOADFILES]</td>
	</tr>
	<tr>
		<td style="font-weight: bold">合同状态</td>
		<td colspan="3"><input type="radio" name="contractstatus" id="contractstatus" value="1" onclick="showfilestatus(this)" [GD1] />
		<label>已归档</label> 
		<input type="radio" name="contractstatus" id="contractstatus" value="2" onclick="showfilestatus(this)" [GD2] />
		<label>未归档</label> &nbsp; 
		<span id="showfilereason" [SHOWFILEREASON] >理由&nbsp; &nbsp; <input type="text" style="height:20px;" class="validate[optional,maxSize[500]]" width="300" name="contractstatusreason" id="contractstatusreason" value="[FILEREASON]" /></span></td>
	</tr>
	<tr>
		<td style="font-weight: bold">选择应用流程</td>
		<td colspan="3">
		<ul style="border: 0; width: 100%;" id="pcidlist">
			[PROCESSLIST]
		</ul>
		<div style="clear: both; padding: 4px; border: solid 1px #cecece; background: #efefef;"></div>
		</td>
	</tr>
</table>
<script>
var daili_count = [VARDAILICOUNT];
var mediatf_count = [VARMEDIACOUNT];
var service_count = [VARSERVICECOUNT];
var fw_amount_options = '[FWAMOUNTOPTIONS]';
var baozhengjin_count = [VARBAOZHENGJINCOUNT];
</script>