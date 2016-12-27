<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 财务管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]script/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
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
            	<li><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=1">待审核待打印</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=3">已打印待归档</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoicelist&d=2">当月已审核</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoice_search">开票信息查询</a></li>
        <li><a href="[BASE_URL]finance/deposit/?o=deposit_invoice_import">开票信息导入</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivableslist">当月保证金收款列表</a></li>
		        <li class="on"><a>保证金收款编辑</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivables_search">保证金收款信息查询</a></li>
		        <li><a href="[BASE_URL]finance/deposit/?o=deposit_receivables_import">保证金收款信息导入</a></li>
			</ul>
		</div>
        <div class="publicform fix" style="display:[NONE1]">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
            	<tr>
					<td align="center"><font color="#FF0000">您当前没有权限访问！</font></td>
				</tr>
			</table>
		</div> 
        <div class="publicform fix" style="display:[NONE2]">
        	<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
        	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1" style="white-space:nowrap">
            	<tr>
					<td style="font-weight:bold;width:100px">收款日期</td>
                    <td>
                    	<input type="text" class="validate[required] text Wdate" style="width:100px" name="date" id="date" onclick="WdatePicker()" value="[DATE]"/>
                    </td>
                </tr>
                <tr>
					<td style="font-weight:bold;">收款金额</td>
                    <td>
                    	<input type="text" class="validate[required,custom[invoiceMoney],funcCall[check_rece_amount]] text" style="width:100px" id="amount" name="amount" value="[AMOUNT]"/>
                    </td>
				</tr>
				<tr>
					<td style="font-weight:bold;">付款人名称</td>
                    <td>
                    	<input type="text" class="validate[required,maxSize[50]] text" style="width:200px" id="payer" name="payer" value="[PAYER]"/>
                    </td>
				</tr>
                <tr>
					<td style="font-weight:bold;">
                    	关联执行单
                    </td>
                    <td>
                    	<table width="750" cellpadding="0" cellspacing="0" border="0" class="sbd1" >
                    		<thead>
                    			<tr><td width="150px;"><b>收款额</b></td><td width="150px;"><b>合同号</b></td><td width="300px;"><b>客户名称</b></td><td width="150px;"><b>应收账款</b></td></tr>
                      		 </thead>
                       		<tbody id="pidinfo">[PIDLIST]</tbody>
                         </table>
                         <br />
                         <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1" id="searhpidinfo">
							<tr>
                                <td>
                                	关键字： &nbsp;
                                    <input type="text" class="text" style="width:150px" id="search_content" /> 
                                    <input type="button" value="&nbsp;按发票号码搜索&nbsp;" onclick="dosearch(1,1)" class="longbtn"/>
                                    &nbsp;
                                    <input type="button" value="&nbsp;按客户名称或合同号搜索&nbsp;" onclick="dosearch(2,1)" class="longbtn"/>
                                </td>
                            </tr>
                            <tr>
                                <td style="white-space:nowrap">
                                <div id="search_result">
                                </div>
                                </td>
                            </tr>
                        </table>
                    </td>    
				</tr>
			</table>
            <div class="btn_div">
				<input type="hidden" name="update_id" id="update_id" value="[UPDATEID]"/><input type="hidden" name="pids" id="pids" value="[PIDS]"/><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="update_deposit_receivables"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
			</div>
			</form>
			<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
            <br />  
		</div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]script/ajaxfileupload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.autocomplete.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" ></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = '[BASE_URL]';
var vcode = '[VCODE]';

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

function removepid(v,pid){
	$(v).parent().parent().remove();
	var nowpids = $("#pids").val();
	$("#pids").val(nowpids.replace("," + pid + ",",","));
	checksum();
}

function get_fix(val){
    var temp = Math.pow(10,2);
    var s = Math.ceil(val * temp)
    return s/temp;
}

function checksum(){
	var x = 0;
	$('[id^=amount1_]').each(function(){
		x += Number($(this).val());
	});
	$("#sumshow").html("<font color=\"red\">" + get_fix(x) + "</font>");
}

function pidmove(){
	var tmps,s
	var pids = "";
	$(":checkbox").each(function(index, element) {
		if ($(this).attr("checked")=="checked"){
			var nowpids = $("#pids").val();
			if(nowpids.indexOf("," + $(this).val() + ",") == -1){
				var _pid = "#pid_" + $(this).val();
				var _cus = "#cus_" + $(this).val();
				var _rece = "#rece_" + $(this).val();
				tmps = "<td>" + $( _pid).html() + "</td><td>" + $(_cus).html() + "</td><td><font color=\"red\">" + $(_rece).html() + "</font></td>";
				s = '<tr><td><img src="' + base_url + 'images/close.png" onclick="removepid(this,\'' + $(this).val() + '\')" width="12" height="12" />&nbsp;<input type="text" class="validate[required,custom[invoiceMoney]] text" style="width:100px; height:12px" name="amount1_' + $(this).val() + '" id="amount1_' + $(this).val() + '" /></td>' + tmps + "</tr>";
				$("#pidinfo").append(s);
				$("#pids").val(nowpids + $(this).val() + ",");
			}
		}
	});
}

function dosearch(type,page){
	$.ajax({
		type: "POST",
		url: "do.php",
		cache:"false",
		data: "action=search_deposit_for_receivables&type=" + type + "&page=" + page + "&search=" + $("#search_content").val() + "&t=" + Math.random() + "&vcode=" + vcode,
		dataType:'text',
		async: false,
		success: function(msg){
			$("#search_result").empty();
			$("#search_result").append(msg);
		},
	 	error: function(e){
	 		alert("搜索保证金记录异常");
	 	}
	});
}

function opendetail(id){
	$('[id^=det_]').hide();
	var dd = "det_" + id;
	$("#" + dd).show();
}

function check_rece_amount(field, rules, i, options){
	var allval = field.val();
	var addval = 0;
	$('[id^=amount1_]').each(function(){
		addval += Number($(this).val());
	});

	if(Number(allval) != addval){
		return "收款金额必须等于各保证金收款的总合";
	}
}
</script>
</body>
</html>
