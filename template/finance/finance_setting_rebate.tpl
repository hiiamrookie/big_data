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
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/default/easyui.css"/>
<link rel="stylesheet" type="text/css" href="[BASE_URL]js/easyui/themes/icon.css"/>
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">财务管理 - 返点比例设置</div>
		<div class="tab" id="tab" style="height:30px">
        	<ul>
        		<li class="on"><a>返点比例设置</a></li>
        		<li><a href="[BASE_URL]finance/?o=rebate_list">返点比例列表</a></li>
        		<li><a href="[BASE_URL]finance/?o=rebate_export">返点比例导出</a></li>
      		</ul>
		</div>
       <div class="publicform fix">
			<form id="formID" method="post" action="[BASE_URL]finance/action.php" target="post_frame">
			<table class="sbd1" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="font-weight:bold" width="150">供应商名称</td>
                    <td>
                    	<select name="supplier_name" id="supplier_name" style="width:200px;">
							[SUPPLIERNAMES]
						</select>
					</td>
                </tr>
                <tr>
                    <td style="font-weight:bold" width="150">媒体简称</td>
                    <td><select name="media_short" id="media_short" style="width:200px;">
							[SUPPLIERSHORTNAMES]
						</select></td>
                </tr>
                <tr>
                    <td style="font-weight:bold" width="150">产品分类</td>
                    <td>
                    	<!-- select id="categorytype" name="categorytype" class="select" style="width:200px;"></select> -->
                    	<input id="categorytype" name="categorytype" style="width:200px;"/>
                    </td>
                </tr>
                 <tr>
                    <td style="font-weight:bold" width="150">客户行业分类</td>
	                <td>
	               		<select id="industrytype" name="industrytype" class="select" style="width:200px;"></select>
	               </td>
                </tr>
                 <tr>
                    <td style="font-weight:bold" width="150">返点比例</td>
                    <td><input type="text" name="rebate"  id="rebate" class="validate[required,custom[number],min[0],max[100]] text_new" style="width: 40px;"/>&nbsp;%</td>
                </tr>
            </table>
            <div class="btn_div"><input type="hidden" name="vcode" id="vcode" value="[VCODE]"/><input type="hidden" name="action" value="add_rebate"/><input type="submit" value="确定" class="btn_sub" id="sub" /></div>
            </form>
            <iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
		</div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script src="[BASE_URL]js/jquery.validationEngine.js" charset="utf-8"></script>
<script src="[BASE_URL]js/languages/jquery.validationEngine-zh_CN.js" charset="utf-8"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/upload.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/js.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]finance/finance.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="[BASE_URL]js/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
var vcode = "[VCODE]";
$(document).ready(function() {
	$("#formID").validationEngine("attach",{ 
		validationEventTrigger: "",
		autoHidePrompt:true,
		autoHideDelay:3000,
	    success: false,
	    promptPosition:"bottomRight", 
	    scroll:false
	});

	$("#categorytype").combobox({
		valueField:'id',
		textField:'text',
		editable:false
	});

	$("#industrytype").combobox({
		valueField:'id',
		textField:'text',
		editable:false
	});

	$("#media_short").combobox({
		onSelect:function(newValue,oldValue){
			$.ajax({
				   type: "POST",
				   url: "do.php",
				   cache:false,
				   data: "action=get_industry_by_suppliershortid&sid=" + $('#media_short').combobox('getValue') + "&t=" + Math.random() + "&vcode=" + vcode,
				   dataType:'text',
				   async: false,
				   success: function(msg){
						   msg = $.parseJSON(msg);
						   var industry = msg.industry;
						  
						   if(industry !=""){
							   var is = [{"text":"","id":0}];
							   $.each(industry,function(name,value) { 
								   is.push({"text":value,"id":name});
								});
							  $("#industrytype").combobox("loadData",is);
							  $("#industrytype").combobox("clear");
							}else{
								$("#industrytype").combobox("loadData",{"text":"","id":0});
								$("#industrytype").combobox("clear");
							}	
				   },
			 	   error: function(e){
			 		   alert("获取供应商产品分类出错");
			 	   }
			});
		}
	});
	
	$("#supplier_name").combobox({
		onSelect:function(newValue,oldValue){
			$.ajax({
				   type: "POST",
				   url: "do.php",
				   cache:false,
				   data: "action=get_category_by_supplierid&sid=" + $('#supplier_name').combobox('getValue') + "&t=" + Math.random() + "&vcode=" + vcode,
				   dataType:'text',
				   async: false,
				   success: function(msg){
					  // alert(msg);
						   msg = $.parseJSON(msg);
						   var category = msg.category;
						   if(category !=""){
							   var cs = [{"text":"","id":0}];
							   $.each(category,function(name,value) { 
								   cs.push({"text":value,"id":name});
								});
							  $("#categorytype").combobox("loadData",cs);
							  $("#categorytype").combobox("clear");
							}else{
								$("#categorytype").combobox("loadData",{"text":"","id":0});
								$("#categorytype").combobox("clear");
							}

							/*
						   var industry = msg.industry;
						  
						   if(industry !=""){
							   var is = [{"text":"","id":0}];
							   $.each(industry,function(name,value) { 
								   is.push({"text":value,"id":name});
								});
							  $("#industrytype").combobox("loadData",is);
							}else{
								$("#industrytype").combobox("loadData",{"text":"","id":0});
								$("#industrytype").combobox("clear");
							}
							*/

							
				   },
			 	   error: function(e){
			 		   alert("获取供应商产品分类出错");
			 	   }
			});
		}
	});
});
</script>
</body>
</html>
