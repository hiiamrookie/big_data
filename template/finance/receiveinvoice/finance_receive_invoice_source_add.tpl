<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/validationEngine.jquery.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
  <div class="nav_top">[TOP]</div>
  <div id="content" class="fix">
    <div class="crumbs">财务管理</div>
    <div class="tab">
      <ul>
      		<li><a href="?o=receiveinvoicelist">收票对账单信息列表</a></li>
			<li class="on"><a>新建收票对账单信息</a></li>
        	<li><a href="?o=receiveinvoiceimport">收票对账单信息导入</a></li>  	
        	<li><a href="?o=pidsharelist">已分配执行单记录</a></li>
        	<li><a href="?o=paymentsharelist">已分配付款申请记录</a></li>
        	<li><a href="?o=virtualinvoiceshare">虚拟发票分配执行单</a></li>
        	<li><a href="?o=virtualinvoicesharepayment">虚拟发票分配付款申请</a></li>
      </ul>
    </div>
    <div class="publicform fix">
    <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
        <tr>
          <td style="font-weight:bold;width:150px">纳税人识别号</td>
          <td><input type="text" name="taxpayer_number" id="taxpayer_number" style="width:200px;" class="validate[required,maxSize[255]] text_new"/></td>
        </tr>
        <tr>
          <td style="font-weight:bold;width:150px">媒体名称</td>
          <td><input type="text" name="media_name" id="media_name" style="width:200px;" class="validate[required,maxSize[255]] text_new"/></td>
        </tr>
        <tr>
          <td style="font-weight:bold;">发票内容</td>
          <td>
          	<select name="invoice_content" class="validate[required] select">
          		<option value="1">广告</option>
          		<option value="2">服务</option>
          	</select><!-- input type="text" name="invoice_content" id="invoice_content" style="width:200px;" class="validate[required,maxSize[255]] text_new"/--></td>
        </tr>
        <tr>
          <td style="font-weight:bold;">凭证号码</td>
          <td><input type="text" name="invoice_number" id="invoice_number" style="width:200px;" class="validate[required,maxSize[255]] text_new"/></td>
        </tr>
        <tr>
          <td style="font-weight:bold">税率</td>
          <td><input type="text" name="tax_rate" id="tax_rate" style="width:35px;" class="validate[required,custom[number],max[100],min[0]] text_new" onblur="javascript:check_rate(this);" value="0.00"/>&nbsp;%</td>
        </tr>
        <tr>
          <td style="font-weight:bold">成本</td>
          <td><input type="text" name="amount" id="amount" style="width:200px;" class="validate[required,custom[money]] text_new" value="0.00" onblur="javascript:check_amount(this);"/>&nbsp;元</td>
        </tr>
        <tr>
          <td style="font-weight:bold">进项</td>
          <td><span id="taxshow">0.00</span>&nbsp;元</td>
        </tr>
        <tr>
          <td style="font-weight:bold">价税合计金额</td>
          <td><span id="sumamountshow">0.00</span>&nbsp;元</td>
        </tr>
        <tr>
          <td style="font-weight:bold">发票日期</td>
          <td><input type="text" class="validate[required] text Wdate" name="invoice_date" id="invoice_date" style="width:100px" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});"/></td>
        </tr>
        <tr>
          <td style="font-weight:bold">所属月份</td>
          <td><input type="text" class="validate[required] text Wdate" name="belong_month" id="belong_month" style="width:100px" onclick="WdatePicker({dateFmt:'yyyy-MM'});"/></td>
        </tr>
      </table>
      <div class="btn_div">
        <input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="receive_invoice_source_add"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
      </div>
      </form>
      <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    </div>
  </div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script>
$(document).ready(function(){
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	})	
});
</script>
</body>
</html>
