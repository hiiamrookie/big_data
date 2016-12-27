<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
    <tr>
        <td style="font-weight:bold" width="150">流程进度</td>
        <td><b>[PROCESSLIST]</b></td>
    </tr>
    <tr>
        <td style="font-weight:bold">执行单号</td>
        <td><b>[PID]</b></td>
    </tr>
    <tr>
        <td style="font-weight:bold">项目名称</td>
        <td>[NAME]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">创建日期</td>
        <td>[TIME]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">执行单类型</td>
        <td>[TYPE]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">所属地区/部门/团队</td>
        <td>[CITYINFO]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">所属合同</td>
        <td>[CID]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">签约客户名称</td>
        <td>[CUSNAME]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">签约公司</td>
        <td>[COMPANY]</td>
    </tr>  
    <tr>
        <td style="font-weight:bold">附件</td>
        <td>[DIDS]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">项目负责人</td>
        <td>[PRINCIPAL]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">项目执行人</td>
        <td>[ACTOR]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">项目实际执行期</td>
        <td>[EXECUTIME]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">项目总金额</td>
        <td><font color="#ff9933"><b>[ALLAMOUNT]</b></font></td>
    </tr>
    <tr>
        <td style="font-weight:bold">成本总金额</td>
        <td><font color="#ff9933"><b>[ALLCOST]</b></font></td>
    </tr>
    <tr>
        <td style="font-weight:bold">合同约定付款日期</td>
        <td>[PAYTIMEINFO]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">税前、税费金额拆分</td>
        <td>[SERVICECFINFO]</td>
    </tr>      
    <tr>
        <td style="font-weight:bold">备注</td>
        <td>[REMARK]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">创建人</td>
        <td>[USER]</td>
    </tr>
    <tr>
        <td style="font-weight:bold">支持部门</td>
        <td>[SUPPORT]</td>
    </tr>
</table>
<br />
<table cellpadding="0" cellspacing="0" border="0" class="sbd1" width="100%">
    <tr>
        <td colspan="2" style="font-weight:bold">[DEPNAME] - 成本信息</td>
    </tr>
    <tr>
        <td style="font-weight:bold; width:150px">成本明细</td>
        <td>
        	总：<font color="#ff9933"><b>[COST]</b></font>
            <br />
        	[COSTINFO]
        </td>
    </tr>
     <tr>
     	<td style="font-weight:bold">成本支付明细</td>
      		<td class="copydate">
            	成本明细支付总额 <b><span style="color:#ff9933; font-size:14px" id="costpayment">[COSTPAYMENT]</span></b> 元<br />
               	<input type="button" id="addcostpayment" value="添 加" class="btn"/>&nbsp;&nbsp;<!--input type="button" id="addcybtn" value="&nbsp;拆月信息&nbsp;" class="longbtn"/-->
				[COSTPAYMENTINFO]
         </td>
    </tr>
    <tr>
    	<td style="font-weight:bold;" width="150">执行成本拆月</td>
    	<td><input type="button" id="addcybtn" value="&nbsp;拆月信息&nbsp;" class="longbtn"/></td>
    </tr>
</table>
