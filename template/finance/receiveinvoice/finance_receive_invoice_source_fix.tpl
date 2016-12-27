<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 财务管理</title>
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
      		<li class="on"><a>收票对账单信息修改</a></li>
        	<li><a href="?o=receiveinvoicelist">收票对账单信息列表</a></li>      	
      </ul>
    </div>
    <div class="publicform fix">
    <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
     <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd11">
    	<tr>
          <td style="font-weight:bold;width:150px">&nbsp;</td>
        	<td width="45%">现有数据</td>
        	<td width="45%">最新数据</td>
        </tr>
        </table>
      <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
        <tr>
          <td style="font-weight:bold;width:150px">纳税人识别号</td>
        	<td width="45%">[TAXPAYERNUMBER]</td>
        	<td width="45%">[TAXPAYERNUMBER1]</td>
        </tr>
        <tr>
          <td style="font-weight:bold;width:150px">媒体名称</td>
          	<td width="45%">[MEDIANAME]</td>
        	<td width="45%">[MEDIANAME1]</td>
        </tr>
        <tr>
          <td style="font-weight:bold;">发票内容</td>
          	<td width="45%">[INVOICECONTENT]</td>
        	<td width="45%">[INVOICECONTENT1]</td>
        </tr>
        <tr>
          	<td style="font-weight:bold;">凭证号码</td>
          	<td width="45%">[INVOICENUMBER]</td>
        	<td width="45%">[INVOICENUMBER1]</td>
        </tr>
        <tr>
          	<td style="font-weight:bold">税率</td>
          	<td width="45%">[TAXRATE] %</td>
        	<td width="45%">[TAXRATE1] %</td>
        </tr>
        <tr>
          	<td style="font-weight:bold">成本</td>
         	 <td width="45%">[AMOUNT]&nbsp;元</td>
        	<td width="45%">[AMOUNT1]&nbsp;元</td>
        </tr>
        <tr>
          	<td style="font-weight:bold">进项</td>
          	<td width="45%">[TAX]&nbsp;元</td>
        	<td width="45%">[TAX1]&nbsp;元</td>
        </tr>
        <tr>
          	<td style="font-weight:bold">价税合计金额</td>
          	<td width="45%">[SUMAMOUNT]</span>&nbsp;元</td>
        	<td width="45%">[SUMAMOUNT1]</span>&nbsp;元</td>
        </tr>
        <tr>
         	 <td style="font-weight:bold">发票日期</td>
          	<td width="45%">[INVOICEDATE]</td>
        	<td width="45%">[INVOICEDATE1]</td>
        </tr>
        <tr>
          	<td style="font-weight:bold">所属月份</td>
          	<td width="45%">[BELONGMONTH]</td>
        	<td width="45%">[BELONGMONTH1]</td>
        </tr>
      </table>
      <div class="btn_div">
        <input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="receive_invoice_source_fix"/><input type="hidden" name="sourceid" id="sourceid" value="[SOURCEID]"/><input type="hidden" name="actype" id="actype" value="update"/><input type="button" value="覆盖现有数据" class="btn_sub" id="upsubmit" />&nbsp;<input type="button" value="删除最新数据" class="btn_sub" id="delsubmit" />
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
	$(".sbd1 tr").each(function(index, element) {
		if ($(this).children().eq(2).html()==null) return true;
		if ($(this).children().eq(1).html()!=$(this).children().eq(2).html()) $(this).addClass("bw1"); 
	});

	$("#upsubmit").click(function(){
		$("#actype").val("update");
		$("#formID").submit();
	});

	$("#delsubmit").click(function(){
		$("#actype").val("del");
		$("#formID").submit();
	});
});
</script>
</body>
</html>
