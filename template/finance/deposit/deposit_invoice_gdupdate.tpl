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
    <div class="crumbs">财务管理 - 开票审核</div>
    <div class="tab">
      <ul>
        <li class="on"><a>查阅开票</a></li>
        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=2">当月已审核</a></li>
      </ul>
    </div>
    <div class="publicform fix">
    <form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
        <tr>
          <td style="font-weight:bold;width:150px">申请时间</td>
          <td>[TIME]</td>
        </tr>
        <tr>
          <td style="font-weight:bold;width:150px">开票金额</td>
          <td><font color="#ff9933"><b>[AMOUNT]</b></font> 元</td>
        </tr>
        <tr>
          <td style="font-weight:bold;">发票类型</td>
          <td>[INVOICETYPE]</td>
        </tr>
        <tr>
          <td style="font-weight:bold;">开票类型</td>
          <td>[TYPE]</td>
        </tr>
        <tr>
          <td style="font-weight:bold">开票抬头</td>
          <td>[TITLE]</td>
        </tr>
        <tr>
          <td style="font-weight:bold">开票内容</td>
          <td>[CONTENT]</td>
        </tr>
        <tr>
          <td style="font-weight:bold">备注</td>
          <td>[REMARK]</td>
        </tr>
        <tr>
          <td style="font-weight:bold">所属执行单</td>
          <td>[PIDINFO]</td>
        </tr>
        <tr>
          <td style="font-weight:bold">开票日期</td>
          <td><input type="text" class="validate[required] text Wdate" style="width:150px" name="date" id="date" onclick="WdatePicker()" value="[DATE]"/></td>
        </tr>
        <tr>
          <td style="font-weight:bold">开票号码</td>
          <td><input type="text" class="validate[required,maxSize[500]] text" style="width:150px" name="number" id="number" value="[NUMBER]"/></td>
        </tr>
        <tr>
          <td style="font-weight:bold">审核状态</td>
          <td>[AUDITMSG]</td>
        </tr>
      </table>
		<div class="btn_div">
			 <input type="hidden" name="invoiceid" id="invoiceid" value="[ID]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" id="action" value="deposit_invoice_gdupdate"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
      	</div>
      </form>
      <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
    </div>
  </div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
$(document).ready(function() {	
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});
});
</script>
</body>
</html>
