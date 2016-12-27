<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理</div>
		<div class="tab" id="tab">
			<ul>
        		<li class="on"><a>供应商信息申请详情</a></li>
        		<li><a href="[BASE_URL]finance/supplier/?o=mylist">已申请供应商信息列表</a></li>
			</ul>
		</div>
       <div class="publicform fix">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
                <tr>
                    <td style="font-weight:bold;width:100px">供应商名称</td>
                    <td>[SUPPLIERNAME]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">网址</td>
                    <td>[URL]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">是否有抵扣联</td>
                    <td>[DEDUCTION]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">进票税率</td>
                    <td>[ININVOICETAXRATE]&nbsp;%</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">供应商类型</td>
                    <td>[SUPPLIERTYPE]</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">附件上传</td>
                    <td>
						<div>[DIDS]</div>
					</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;width:100px">申请状态</td>
                    <td>[STATUS]</td>
                </tr>
                [REMARK]
            </table>
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
</body>
</html>