<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
       			<tr>
          			<td style="font-weight:bold; width:150px">媒体名称</td>
          			<td>
            			[MEDIANAME]
          			</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold; width:150px">开户行</td>
          			<td>
            			[BANKNAME]
          			</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold; width:150px">银行账号</td>
          			<td>
            			[ACCOUNTNAME]
          			</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;">应付金额</td>
          			<td>
          				[PAYMENTAMOUNTPLAN] 元
          			</td>
       	 		</tr>
      			<tr>
          			<td style="font-weight:bold;width:150px">付款时间</td>
          			<td>[PAYMENTDATE]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">是否垫付</td>
          			<td>[ISNIMPAYFIRST]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">返点抵扣</td>
          			<td>[ISREBATEDEDUCTION]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">返点金额</td>
          			<td>[REBATEAMOUNT] 元</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">保证金抵扣</td>
          			<td>[ISDEPOSITDEDUCTION]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">个人借款抵扣</td>
          			<td>[ISPERSONLOANDEDUCTION]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">个人借款金额</td>
          			<td>[PERSONLOANAMOUNT] 元</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">合同款抵扣</td>
          			<td>[ISCONTRACTDEDUCTION]</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">实付金额</td>
          			<td><b><span style="color:#ff9933; font-size:14px" id="actually_paid">[PAYMENTREAL]</span></b> 元</td>
        		</tr>
        		<tr>
          			<td style="font-weight:bold;width:150px">备注</td>
          			<td>[REMARK]<input type="hidden" name="payment_id" id="payment_id" value="[PAYMENTID]"/></td>
        		</tr>
        		</table>
        		<p></p>
        		<table class="easyui-datagrid" id="dg" data-options="
      			title:'所选媒体数据',
      			autoRowHeight:true,
      			striped:true,
      			toolbar:'#toolbar'">
      			<thead>
					<tr>
						[ISCK]
		                <th data-options="field:'a',width:100" rowspan="2">合同号</th>
		                <th data-options="field:'b',width:200" rowspan="2">客户名称</th>
		                 <th data-options="field:'c',width:200" rowspan="2">已收客户保证金金额</th>
		                <th data-options="field:'d',width:200,align:'right'" rowspan="2">上次已付媒体保证金合计</th>
		                <th data-options="field:'e',width:200,align:'right'" rowspan="2">已付保证金合计</th>
		                <th data-options="field:'f',width:200,align:'right'" rowspan="2">本次申请媒体保证金应付金额</th>
						<th data-options="field:'g',width:400,align:'right'" rowspan="2">返点抵扣</th>
						<th colspan="3">返点</th>
						<th data-options="field:'k',width:400,align:'right'" rowspan="2">个人借款抵扣</th>
						<!-- th data-options="field:'l',width:200,align:'right'" rowspan="2">实付金额</th -->
						<th data-options="field:'m',width:450" rowspan="2" >是否垫付</th>
						[ISAUDIT]
					</tr>
            		<tr>
			        	<th data-options="field:'h',width:200,align:'right'">待开票返点</th>
			        	<th data-options="field:'i',width:200,align:'right'">已开票返点</th>
			           	<th data-options="field:'j',width:200,align:'right'">无需开票返点</th>
			        </tr>
       			 </thead>		   
				</table>
				
        		<!--table class="easyui-datagrid" id="dg" style="height:auto" data-options="
      			title:'所选媒体数据',
      			autoRowHeight:true,
      			striped:true,
      			rownumbers:true">
      			<thead>
					<tr>
						[ISCK]
		                <th data-options="field:'a',width:100" rowspan="2">执行单号</th>
		                <th data-options="field:'b',width:200" rowspan="2">客户名称</th>
		                 <th data-options="field:'c',width:200" rowspan="2">项目名称</th>
		                <th data-options="field:'d',width:200,align:'right'" rowspan="2">客户执行收入</th>
		                <th data-options="field:'e',width:200,align:'right'" rowspan="2">已收客户款合计金额</th>
		                <th data-options="field:'f',width:200,align:'right'" rowspan="2">已开票合计金额</th>
		                <th data-options="field:'g',width:200,align:'right'" rowspan="2">已执行未到客户款金额</th>
		                <th data-options="field:'h',width:200" rowspan="2">供应商</th>
						<th data-options="field:'i',width:200,align:'right'" rowspan="2">媒体执行成本</th>
						 <th data-options="field:'j',width:200,align:'right'" rowspan="2">已执行未付成本金额</th>
						<th data-options="field:'k',width:300,align:'right'" rowspan="2">本次申请金额</th>
						<th data-options="field:'l',width:200,align:'right'" rowspan="2">已付成本合计金额</th>
						<th data-options="field:'m',width:400,align:'right'" rowspan="2">返点抵扣</th>
						<th colspan="3">返点</th>
						<th data-options="field:'q',width:200,align:'right'" rowspan="2">虚拟发票合计金额</th>
						<th data-options="field:'r',width:200,align:'right'" rowspan="2">真实发票到票合计金额</th>
						<th data-options="field:'s',width:200,align:'right'" rowspan="2" >已付款未到票金额</th>
						<th data-options="field:'t',width:400,align:'right'" rowspan="2">个人借款抵扣</th>
						<th data-options="field:'u',width:200,align:'right'" rowspan="2">实付金额</th>
						<th data-options="field:'v',width:450" rowspan="2" >是否垫付</th>
						[ISAUDIT]
					</tr>
            		<tr>
			        	<th data-options="field:'n',width:200,align:'right'">待开票返点</th>
			        	<th data-options="field:'o',width:200,align:'right'">已开票返点</th>
			           	<th data-options="field:'p',width:200,align:'right'">无需开票返点</th>
			        </tr>
       			 </thead>		   
				</table-->
				<p/>
				<table id="selectdeposit" style="width: 100%"></table>