$(document).ready(function() {
	$("#side_nav h2").click(function(){
		if($(this).hasClass("current")){return;}
		else{
			$("#side_nav h2").removeClass("current");
			$("#side_nav ul").removeClass("pane");
			$("#side_nav ul").slideUp("fast");
			$(this).addClass("current");
			$(this).next("ul").addClass("pane");
			$(this).next("ul").slideDown(0);
		}
	}).eq(menu_r).click();
});

var Digit = {};
Digit.round = function(digit, length) {
    length = length ? parseInt(length) : 0;
    if (length <= 0) return Math.round(digit);
    digit = Math.round(digit * Math.pow(10, length)) / Math.pow(10, length);
    return digit;
};

function get_fix(val){
    var temp = Math.pow(10,2);
    var s = Math.ceil(val * temp)
    return s/temp;
}

// 增加合同约定付款时间
function addpaytime(){
	var s="<div>";
		s+="时间：<input type=\"text\" onclick=\"WdatePicker()\" width=\"100\" class=\"validate[required] Wdate\" name=\"paytime_" + pay_count + "\" id=\"paytime_" + pay_count + "\"> &nbsp;&nbsp;"; 
		s+="金额：<input type=\"text\" onblur=\"tjamount(this)\" style=\"width:100px; text-align:right;height:20px;\" class=\"validate[required,custom[money]]\" name=\"payamount_" + pay_count + "\" id=\"payamount_" + pay_count + "\"> 元&nbsp;&nbsp;";
		s+="备注：<input type=\"text\" width=\"150\" style=\"height:20px;\" name=\"payremark_" + pay_count + "\" id=\"payremark_" + pay_count + "\">&nbsp;&nbsp;";
		s+="<img src=\"../images/close.png\" onclick=\"delpaytime(this,'" + pay_count + "')\" width=\"17\" height=\"17\" /><br /></div>";
		
		$("#paytimelist").append(s);
		var paycount = $("#paycount").val();
		$("#paycount").val(paycount + pay_count + ",");
		pay_count++;
}

function delpaytime(v,pay_count){ 
  $(v).parent().remove();
  
  var amount=0;
  $("#paytimelist").children().each(function(index, element) {
    amount+=Number($(this).children().eq(1).val());
  });
  
  $("#amount").text(amount.toFixed(2));
  
  var paycount = $("#paycount").val();
  $("#paycount").val(paycount.replace("," + pay_count + ",",","));
}


// 增加服务金额拆分
function addservicecf(){
	var add = false;
	if(tax_rate_718 == 0){
		//根据合同获取税点
		var cid = $.trim($("#cid").val());
		if(cid !=""){
			cid = cid.split("-");
			$.ajax({
				   type: "POST",
				   url: "do.php",
				   cache:false,
				   data: "action=getTaxRate&cid=" + cid[0] + "&t=" + Math.random() + "&vcode=" + vcode,
				   dataType:'text',
				   async: false,
				   success: function(msg){
					   tax_rate_718 = msg;
					   if(tax_rate_718 != 0){
						   add = true;
					   }
				   },
			 	   error: function(e){
			 		   alert("获取税点信息出错");
			 		   return;
			 	   }
			});
		}else{
			alert("请先选择“所属合同”");
			return;
		}	  
	}else{
		add = true;
	}
	
	
	if(add){
		$("#taxrate").html(tax_rate_718 * 100 + "%");
		var s="<div><select name=\"servercf_type_" + service_cf_count + "\" class=\"select\">";
		if(tax_rate_718 == 0.0683){
			var tmp_tax_rate_select = tax_rate_select;
			tmp_tax_rate_select = eval("("+tmp_tax_rate_select+")");
			for(var n=0;n<tmp_tax_rate_select.length;n++){
				s += "<option value=\"" + tmp_tax_rate_select[n].id + "\">" + tmp_tax_rate_select[n].name + "</option>";
			}
		}else{
			s += "<option value=0>无</option>";
		}
		  
		s+="</select>&nbsp;&nbsp;";
		s+="金额：<input name=\"servercf_amount_" + service_cf_count + "\" id=\"servercf_amount_" + service_cf_count + "\" type=\"text\" style=\"width:100px; text-align:right;height:20px;\" class=\"validate[required,custom[money]] rb3\" onblur=\"tjservicecf(this)\" /> 元&nbsp;&nbsp;";
		s+="备注：<input name=\"servercf_remark_" + service_cf_count + "\" id=\"servercf_remark_" + service_cf_count + "\" type=\"text\" width=\"150\" style=\"height:20px;\">&nbsp;&nbsp;";
		s+="<img src=\"../images/close.png\" onclick=\"delservicecf(this,'" + service_cf_count + "')\" width=\"17\" height=\"17\" /></div>";

	  $("#servicecflist").append(s);
	  var servicecf = $("#servicecf").val();
	  $("#servicecf").val(servicecf + service_cf_count + ",");
	  service_cf_count++;
	}
}


function delservicecf(v,service_cf_count){ 
  $(v).parent().remove();
  
  var amount=0;
  $("#servicecflist").children().each(function(index, element) {
    amount+=Number($(this).children().eq(1).val());
  });
  amount = Number(amount.toFixed(2));
  $("#allservicecfamount").text(amount);
  var taxamount = amount*tax_rate_718;
  taxamount = Number(get_fix(taxamount));
  $("#taxamount").text(taxamount);
  
  var servicecf = $("#servicecf").val();
  $("#servicecf").val(servicecf.replace("," + service_cf_count + ",",","));
}

// 统计服务金额拆分
function tjservicecf(v){
	if(isNaN(v.value)){
		return;
	}else{
		 var val = v.value;
		 if(val.indexOf(".")!=-1){
			 val = Number(val);
			 v.value = val.toFixed(2); 
		 }	 
	 }
	
	var allamount = Number($("#amount").text());
	if(allamount<=0){
		alert("请先输入合同约定付款金额");
		v.value = "";
	}else{
		var amount=0;
		$("#servicecflist").children().each(function(index, element) {
			amount+=Number($(this).children().eq(1).val());
		});
		amount = Number(amount.toFixed(2));
		$("#allservicecfamount").text(amount);
		var taxamount=amount*tax_rate_718;
		taxamount = Number(taxamount.toFixed(2));
		var o = amount + taxamount  - allamount;
		o = Number(o.toFixed(2));
		if(Math.abs(o) <= 0.01 && Math.abs(o)>0){
			taxamount = allamount- amount;		
			//taxamount = Number(get_fix(taxamount));
			taxamount = Number(taxamount.toFixed(2));
		}	
		$("#taxamount").text(taxamount);
		
	}
}

// 增加一条成本明细
function addcostinfo(){
  var s="<div>";
      s+="<select name=\"costtype_" + cost_count + "\" class=\"select\"><option value=1>媒介成本</option><option value=2>硬件成本</option><option value=3>搜索成本</option><option value=4>效果成本</option><option value=6>媒体公关成本（个人）</option><option value=7>客户返点</option><option value=8>外包成本（公司）</option><option value=9>媒体公关成本（公司）</option></select>&nbsp;&nbsp;";
	  s+="金额：<input type=\"text\" onblur=\"tjcost(this)\" style=\"width:100px; text-align:right;height:20px;\" class=\"validate[required,custom[money]] rb3\" name=\"costamount_" + cost_count + "\" id=\"costamount_" + cost_count + "\"> 元&nbsp;&nbsp;";
	  s+="收款方全称：<input type=\"text\" name=\"costname_" + cost_count + "\" id=\"costname_" + cost_count + "\" class=\"validate[required] text_new\" style=\"height:20px;width:300px;\" onfocus=\"javascript:getSupplierName(this);\">&nbsp;&nbsp;";
	  s+="预估：<select name=\"costyg_" + cost_count + "\" class=\"select\"><option value=0>否</option><option value=1>是</option></select>&nbsp;&nbsp;";
	  s+="<img src=\"../images/close.png\" onclick=\"delcostinfo(this,'" + cost_count + "')\" width=\"17\" height=\"17\" /><br /></div>";
	 
  $("#costinfolist").append(s);
  var costcount = $("#costcount").val();
  $("#costcount").val(costcount + cost_count + ",");
  cost_count++;
}

function delcostinfo(v,cost_count){ 
  $(v).parent().remove();
  
  var amount=0;
  $("#costinfolist").children().each(function(index, element) {
    amount+=Number($(this).children().eq(1).val());
  });
  
  $("#cost").text(amount.toFixed(2));
  
  var costcount = $("#costcount").val();
  $("#costcount").val(costcount.replace("," + cost_count + ",",","));
}

//增加执行金额拆月
function addamountcy(){
	var s = "<div>";
	s += "时间：<input type=\"text\" width=\"100\" class=\"validate[required] Wdate\" name=\"amountcytime_" + cy_amount_count + "\" id=\"amountcytime_" + cy_amount_count + "\" onclick=\"WdatePicker({dateFmt:'yyyy-MM'});\" readonly=\"readonly\"> &nbsp;&nbsp;";
	s += "金额：<input type=\"text\" onblur=\"tjamountcy(this)\" style=\"width:100px; text-align:right;height:20px;\" class=\"validate[required,custom[cyMoney]]\" name=\"amountcy_" + cy_amount_count + "\" id=\"amountcy_" + cy_amount_count + "\"> 元&nbsp;&nbsp;";
	s += "<img src=\"../images/close.png\" onclick=\"delamountcy(this,'" + cy_amount_count + "')\" width=\"17\" height=\"17\" /><br /></div>";
	$("#cyamountlist").append(s);
	var cy_amount = $("#cy_amount").val();
	$("#cy_amount").val(cy_amount + cy_amount_count + ",");
	cy_amount_count++;
}

function delamountcy(v,cyamountcount){
	$(v).parent().remove();
	var amount=0;
	$("#cyamountlist").children().each(function(index, element) {
		amount+=Number($(this).children().eq(1).val());
	});
	  
	$("#amountcy").text(amount.toFixed(2));
	  
	var cy_amount = $("#cy_amount").val();
	$("#cy_amount").val(cy_amount.replace("," + cyamountcount + ",",","));
}

function tjamountcy(v){
	 if(isNaN(v.value)){
		 v.value = 0;
		  return;
	 }else{
		 var val = v.value;
		 if(val.indexOf(".")!=-1){
			 val = Number(val);
			 v.value = val.toFixed(2); 
		 }	 
	 }
	 
	var amount=0;
	$("#cyamountlist").children().each(function(index, element) {
		amount+=Number($(this).children().eq(1).val());
	});
  
	$("#amountcy").text(amount.toFixed(2));
	
}

function addcy(){
	var costpaycount = $("#costpaycount").val();
	var canopen = true;
	if(costpaycount != ","){
		costpaycount = costpaycount.split(",");
		for(var i=0;i<costpaycount.length;i++){
			if(costpaycount[i] != ""){
				var cuid = "costpay_" + costpaycount[i];
				if($.trim($("#" + cuid).val()) == ""){
					canopen = false;
					break;
				}
			}
		}
	}else{
		canopen = false;
	}

	
	if(canopen){
		//supplier category
		var sc = $.parseJSON(supplier_categorys);
		//supplier short
		var ss = $.parseJSON(supplier_shorts);
		
		//costpaycount = costpaycount.split(",");
		
		var ssop = "<option value=\"\">请选择投放媒体</option>";
		for(var def=0;def<ss.length;def++){
			ssop += "<option value=\"" + ss[def].id + "\">" + ss[def].name + "</option>";
		}

		if($.trim($(".scbox").html()) == ""){
			var s = "<div>&nbsp;原执行成本：<span style=\"color:red;font-weight:bold;\">0</span>&nbsp;元&nbsp;现执行成本：<span id=\"coa\" style=\"color:red;font-weight:bold;\">0</span>&nbsp;元</div>";
			s += "<div>&nbsp;<font color=\"red\"><b>(*)</b></font>：指实际投放媒体，例如“Baidu 百度”，“QQ 腾讯” </div>";
			s += "<div>&nbsp;<font color=\"red\"><b>(**)</b></font>：指投放的产品或投放类型，例如“百度_阿拉丁”，“百度_Hao123”， “QQ Live 腾讯视频_OMD”，“QQ 广点通”</div>";
			for(var i=0;i<costpaycount.length;i++){
				if(costpaycount[i] != ""){
					var id = "costpay_" + costpaycount[i];
					var tmp_supplier = suppliers.join(",");
					if(tmp_supplier != ""){
						tmp_supplier = "," + tmp_supplier + ",";
					}
					
					
					//if($("#" + id).val() != "" && tmp_supplier.indexOf("," + $("#" + id).val() + ",") == -1){
					if($("#" + id).val() != ""){
						var op = "<option value=\"\">请选择投放类型</option>";
						for(var abc=0;abc<sc.length;abc++){
							if(sc[abc].s == $("#" + id).val()){
								var xop =sc[abc].d;
								for(var cba=0;cba<xop.length;cba++){
									op += "<option value=\"" + xop[cba] + "\">" + xop[cba] + "</option>";
								}
								break;
							}	
						}
						
						s += "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\" class=\"sbd2\" id=\"table_" + costpaycount[i] + "\">";
						s += "<tr>";
						s += "<td width=\"150\" style=\"font-weight:bold\" rowspan=\"3\">" + $("#" + id).val() + "</td>";
						s += "<td>媒体&nbsp;<font color=\"red\"><b>(*)</b></font></td><td><select name=\"supplier_short_" + costpaycount[i] + "\" id=\"supplier_short_" + costpaycount[i] + "\" class=\"select\">" + ssop + "</select>&nbsp;<select name=\"industry_" + costpaycount[i] + "\" id=\"industry_" + costpaycount[i] + "\" class=\"select\"><option value=\"\">请选择客户行业分类</option></select>&nbsp;<input type=\"button\" class=\"longbtn\" value=\"&nbsp;添加拆月数据&nbsp;\" id=\"addcy_" + costpaycount[i] + "\" onclick=\"javascript:x(this);\"></tr>";
						s += "<tr><td>投放类型&nbsp;<font color=\"red\"><b>(**)</b></font></td><td><input type=\"hidden\" name=\"supplier_" + costpaycount[i] + "\" value=\"" + $("#" + id).val() + "\"><select name=\"deliverytype_" + costpaycount[i] + "\" id=\"deliverytype_" + costpaycount[i] + "\" class=\"select\">" + op + "</select>";
						s += "<input type=\"hidden\" value=\",1,\" id=\"cycount_" + costpaycount[i] + "\" name=\"cycount_" + costpaycount[i] + "\"></td></tr>";
						s += "<tr><td>&nbsp;</td><td>时间：";
						s += "<input type=\"text\" id=\"cyym_" + costpaycount[i] + "_1\" name=\"cyym_" + costpaycount[i] + "_1\" class=\"validate[required] Wdate\" style=\"width:100px;\" onclick=\"WdatePicker({dateFmt:'yyyy-MM'});\" readonly=\"readonly\">";
						//s += "&nbsp;&nbsp;执行成本：<input type=\"text\" id=\"cost_amount_" + costpaycount[i] + "_1\" name=\"cost_amount_" + costpaycount[i] + "_1\" style=\"width:100px;text-align:right;height:20px;\" class=\"validate[required,custom[money]]\">&nbsp;元";
						s += "&nbsp;&nbsp;执行成本：<input type=\"text\" id=\"cost_amount_" + costpaycount[i] + "_1\" name=\"cost_amount_" + costpaycount[i] + "_1\" style=\"width:100px;text-align:right;height:20px;\" class=\"validate[required,custom[cyMoney]]\">&nbsp;元";
						s += "<div id=\"cylist_" + costpaycount[i] + "\">";
						s += "</div></td>";
						s += "</tr>";
						s += "</table>";	
						if(i !=costpaycount.length-1 ){
							s += "<br/>";
						}
						s += "<script>var _cy_" + costpaycount[i] + "=1;</script>";	
						suppliers.push($("#" + id).val());
					}
				}
			}
			if(s != ""){
				s += "<div class=\"btn_div\" id=\"cybtn_sub\"><input type=\"submit\" name=\"cybtn\" value=\"确 定\" class=\"btn_sub\"/></div><script>$(\"#cyform\").validationEngine(\"attach\",{ validationEventTrigger: \"\",autoHidePrompt:true,autoHideDelay:3000,success: false,promptPosition:\"bottomRight\", scroll:false});</script>";
			}
			$(".scbox").html(s);
		}else{
			var news = '';
			for(var xx =0;xx<costpaycount.length;xx++){
				if(costpaycount[xx]!=""){
					var tabid = "table_" + costpaycount[xx];
					if(typeof($("#" + tabid).attr("id")) == "undefined"){
						var id = "costpay_" + costpaycount[xx];
						
						var tmp_supplier = suppliers.join(",");
						if(tmp_supplier != ""){
							tmp_supplier = "," + tmp_supplier + ",";
						}
			
						if($("#" + id).val() != "" && tmp_supplier.indexOf("," + $("#" + id).val() + ",") == -1){
						//if($("#" + id).val() != ""){
							var op = "<option value=\"\">请选择供应商产品分类</option>";
							for(var abc=0;abc<sc.length;abc++){
								if(sc[abc].s == $("#" + id).val()){
									var xop =sc[abc].d;
									for(var cba=0;cba<xop.length;cba++){
										op += "<option value=\"" + xop[cba] + "\">" + xop[cba] + "</option>";
									}
									break;
								}	
							}
							
							news += "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\" class=\"sbd2\" id=\"table_" + costpaycount[xx] + "\">";
							news += "<tr>";
							news += "<td width=\"150\" style=\"font-weight:bold\" rowspan=\"3\">" + $("#" + id).val() + "</td>";
							news += "<td>媒体&nbsp;<font color=\"red\"><b>(*)</b></font></td><td><select name=\"supplier_short_" + costpaycount[xx] + "\" id=\"supplier_short_" + costpaycount[xx] + "\" class=\"select\">" + ssop + "</select>&nbsp;<select name=\"industry_" + costpaycount[xx] + "\" id=\"industry_" + costpaycount[xx] + "\" class=\"select\"><option value=\"\">请选择客户行业分类</option></select>&nbsp;<input type=\"button\" class=\"longbtn\" value=\"&nbsp;添加拆月数据&nbsp;\" id=\"addcy_" + costpaycount[xx] + "\" onclick=\"javascript:x(this);\"></tr>";
							news += "<tr><td>投放类型&nbsp;<font color=\"red\"><b>(**)</b></font></td><td><input type=\"hidden\" name=\"supplier_" + costpaycount[xx] + "\" value=\"" + $("#" + id).val() + "\"><select name=\"deliverytype_" + costpaycount[xx] + "\" id=\"deliverytype_" + costpaycount[xx] + "\" class=\"select\">" + op + "</select>";
							news += "<input type=\"hidden\" value=\",1,\" id=\"cycount_" + costpaycount[xx] + "\" name=\"cycount_" + costpaycount[xx] + "\"></td></tr>";
							news += "<tr><td>&nbsp;</td><td>时间：";
							news += "<input type=\"text\" id=\"cyym_" + costpaycount[xx] + "_1\" name=\"cyym_" + costpaycount[xx] + "_1\" class=\"validate[required] Wdate\" style=\"width:100px;\" onclick=\"WdatePicker({dateFmt:'yyyy-MM'});\" readonly=\"readonly\">";
							//news += "&nbsp;&nbsp;执行成本：<input type=\"text\" id=\"cost_amount_" + costpaycount[xx] + "_1\" name=\"cost_amount_" + costpaycount[xx] + "_1\" style=\"width:100px;text-align:right;height:20px;\" class=\"validate[required,custom[money]]\">&nbsp;元";
							news += "&nbsp;&nbsp;执行成本：<input type=\"text\" id=\"cost_amount_" + costpaycount[xx] + "_1\" name=\"cost_amount_" + costpaycount[xx] + "_1\" style=\"width:100px;text-align:right;height:20px;\" class=\"validate[required,custom[cyMoney]]\">&nbsp;元";
							news += "<div id=\"cylist_" + costpaycount[xx] + "\">";
							news += "</div></td>";
							news += "</tr>";
							news += "</table><br/>";	
							news += "<script>var _cy_" + costpaycount[xx] + "=1;</script>";	
							suppliers.push($("#" + id).val());
						}	
					}
				}
			}
			if(news !=""){
				$("#cybtn_sub").before(news);
				$("#cybtn_sub").after("<script>$(\"#cyform\").validationEngine(\"attach\",{ validationEventTrigger: \"\",autoHidePrompt:true,autoHideDelay:3000,success: false,promptPosition:\"bottomRight\", scroll:false});</script>");
			}
		}
		//alert(suppliers.join(","));
		if($.trim($(".scbox").html()) != ""){
			$(".overlay").before('<div id="exposeMask"></div>').show();
		}
		cost_input();
		getindustry();
	}else{
		alert("请先填写成本支付明细信息");
	}
}

/*
function addcy(){
	var costpaycount = $("#costpaycount").val();
	if(costpaycount != ","){
		costpaycount = costpaycount.split(",");
		if($.trim($(".scbox").html()) == ""){
			var s = "";
			for(var i=0;i<costpaycount.length;i++){
				if(costpaycount[i] != ""){
					var id = "costpay_" + costpaycount[i];
					if($("#" + id).val() != ""){
						s += "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\" class=\"sbd1\" id=\"table_" + costpaycount[i] + "\">";
						s += "<tr>";
						s += "<td width=\"150\" style=\"font-weight:bold\" rowspan=\"2\">" + $("#" + id).val() + "</td>";
						s += "<td>投放类型</td><td><input type=\"text\" name=\"deliverytype_" + costpaycount[i] + "\" id=\"deliverytype_" + costpaycount[i] + "\" style=\"height:20px;\" class=\"validate[required,maxSize[100]]\">&nbsp;<input type=\"button\" class=\"longbtn\" value=\"&nbsp;添加拆月数据&nbsp;\" id=\"addcy_" + costpaycount[i] + "\" onclick=\"javascript:x(this);\">";
						s += "<input type=\"hidden\" value=\",1,\" id=\"cycount_" + costpaycount[i] + "\" name=\"cycount_" + costpaycount[i] + "\"></td></tr>";
						s += "<tr><td>&nbsp;</td><td>时间：";
						s += "<input type=\"text\" id=\"cyym_" + costpaycount[i] + "_1\" name=\"cyym_" + costpaycount[i] + "_1\" class=\"validate[required] Wdate\" style=\"width:100px;\" onclick=\"WdatePicker({dateFmt:'yyyy-MM'});\" readonly=\"readonly\">";
						s += "&nbsp;&nbsp;执行金额：";
						s += "<input type=\"text\" id=\"quote_amount_" + costpaycount[i] + "_1\" name=\"quote_amount_" + costpaycount[i] + "_1\"  style=\"width:100px; text-align:right;height:20px;\" class=\"validate[required,custom[money]]\">";
						s += "&nbsp;元&nbsp;&nbsp;执行成本：<input type=\"text\" id=\"cost_amount_" + costpaycount[i] + "_1\" name=\"cost_amount_" + costpaycount[i] + "_1\" style=\"width:100px;text-align:right;height:20px;\" class=\"validate[required,custom[money]]\">&nbsp;元";
						s += "<div id=\"cylist_" + costpaycount[i] + "\">";
						s += "</div></td>";
						s += "</tr>";
						s += "</table>";	
						if(i !=costpaycount.length-1 ){
							s += "<br/>";
						}
						s += "<script>var _cy_" + costpaycount[i] + "=1;</script>";	
					}
				}
			}
			if(s != ""){
				s += "<div class=\"btn_div\" id=\"cybtn_sub\"><input type=\"submit\" name=\"cybtn\" value=\"确 定\" class=\"btn_sub\"/></div><script>$(\"#cyform\").validationEngine(\"attach\",{ validationEventTrigger: \"\",autoHidePrompt:true,autoHideDelay:3000,success: false,promptPosition:\"bottomRight\", scroll:false});</script>";
			}
			$(".scbox").html(s);
		}else{
			var news = '';
			for(var xx =0;xx<costpaycount.length;xx++){
				if(costpaycount[xx]!=""){
					var tabid = "table_" + costpaycount[xx];
					if(typeof($("#" + tabid).attr("id")) == "undefined"){
						var id = "costpay_" + costpaycount[xx];
						if($("#" + id).val() != ""){
							news += "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\" class=\"sbd1\" id=\"table_" + costpaycount[xx] + "\">";
							news += "<tr>";
							news += "<td width=\"150\" style=\"font-weight:bold\" rowspan=\"2\">" + $("#" + id).val() + "</td>";
							news += "<td>投放类型</td><td><input type=\"text\" name=\"deliverytype_" + costpaycount[xx] + "\" id=\"deliverytype_" + costpaycount[xx] + "\" style=\"height:20px;\" class=\"validate[required,maxSize[100]]\">&nbsp;<input type=\"button\" class=\"longbtn\" value=\"&nbsp;添加拆月数据&nbsp;\" id=\"addcy_" + costpaycount[xx] + "\" onclick=\"javascript:x(this);\">";
							news += "<input type=\"hidden\" value=\",1,\" id=\"cycount_" + costpaycount[xx] + "\" name=\"cycount_" + costpaycount[xx] + "\"></td></tr>";
							news += "<tr><td>&nbsp;</td><td>时间：";
							news += "<input type=\"text\" id=\"cyym_" + costpaycount[xx] + "_1\" name=\"cyym_" + costpaycount[xx] + "_1\" class=\"validate[required] Wdate\" style=\"width:100px;\" onclick=\"WdatePicker({dateFmt:'yyyy-MM'});\" readonly=\"readonly\">";
							news += "&nbsp;&nbsp;执行金额：";
							news += "<input type=\"text\" id=\"quote_amount_" + costpaycount[xx] + "_1\" name=\"quote_amount_" + costpaycount[xx] + "_1\"  style=\"width:100px; text-align:right;height:20px;\" class=\"validate[required,custom[money]]\">";
							news += "&nbsp;元&nbsp;&nbsp;执行成本：<input type=\"text\" id=\"cost_amount_" + costpaycount[xx] + "_1\" name=\"cost_amount_" + costpaycount[xx] + "_1\" style=\"width:100px;text-align:right;height:20px;\" class=\"validate[required,custom[money]]\">&nbsp;元";
							news += "<div id=\"cylist_" + costpaycount[xx] + "\">";
							news += "</div></td>";
							news += "</tr>";
							news += "</table>";	
							news += "<script>var _cy_" + costpaycount[xx] + "=1;</script>";	
						}	
					}
				}
			}
			if(news !=""){
				$("#cybtn_sub").before(news);
				$("#cybtn_sub").after("<script>$(\"#cyform\").validationEngine(\"attach\",{ validationEventTrigger: \"\",autoHidePrompt:true,autoHideDelay:3000,success: false,promptPosition:\"bottomRight\", scroll:false});</script>");
			}
		}
		
		if($.trim($(".scbox").html()) != ""){
			$(".overlay").before('<div id="exposeMask"></div>').show();
		}
	}
}
*/

function cy_json(json){
	$("#cy_json").val(json);	
}

function setcost(id,cost){
	$("#" + id).val(cost);	
}

function x(obj){
	var addid = obj.id;
	addid = addid.split("_");
	
	var x = "cycount_" + addid[1];
	var cycount = $("#" + x).val();
	eval("_cy_" + addid[1] + "++;");
	eval("var cyy =_cy_" + addid[1] + ";");
	$("#" + x).val(cycount + cyy + ",");
	
	var ap = "<div>时间：";
	ap += "<input type=\"text\" id=\"cyym_" + addid[1] + "_" + cyy + "\" name=\"cyym_" + addid[1] + "_" + cyy + "\" class=\"validate[required] Wdate\" style=\"width:100px;\" onclick=\"WdatePicker({dateFmt:'yyyy-MM'});\">";
	//ap += "&nbsp;&nbsp;执行金额：";
	//ap += "<input type=\"text\" id=\"quote_amount_" + addid[1] + "_" + cyy + "\" name=\"quote_amount_" + addid[1] + "_" + cyy + "\"  style=\"width:100px; text-align:right;height:20px;\" class=\"validate[required,custom[money]]\">&nbsp;元";
	ap += "&nbsp;&nbsp;执行成本：<input type=\"text\" id=\"cost_amount_" + addid[1] + "_" + cyy + "\" name=\"cost_amount_" + addid[1] + "_" + cyy + "\" style=\"width:100px;text-align:right;height:20px;\" class=\"validate[required,custom[cyMoney]]\">&nbsp;元&nbsp;&nbsp;<img width=\"17\" height=\"17\" onclick=\"delcy(this," + addid[1] + "," + cyy + ")\" src=\"" + base_url + "/images/close.png\"><br></div>";

	var app = "cylist_" + addid[1];
	$("#" + app).append(ap);	
	
	cost_input();
}

function close_pop(){
	$(".overlay").hide();
	$("#exposeMask").remove();
}

function delcy(obj,cyitem,cynum){
	$(obj).parent().remove();
	var id = "cycount_" + cyitem;
	var cyv = $("#" + id).val();
	$("#" + id).val(cyv.replace("," + cynum + ",",","));
	
	re_cost_input(cyitem,cynum);
}

// 增加一条成本支付明细
function addcostpayment(){
	var s="<div>";
  	if(cy_on){
  		//s+="收款方全称：<select class=\"validate[required] select\" name=\"costpay_" + cost_pay_count + "\" id=\"costpay_" + cost_pay_count + "\" onchange=\"javascript:getcategory(this);\">" + suppliers + "</select>&nbsp;&nbsp;<select style=\"width:200px;\" class=\"validate[required] select\" name=\"costpaycategory_" + cost_pay_count + "\" id=\"costpaycategory_" + cost_pay_count + "\"><option value=\"\">请选择产品分类</option></select>&nbsp;&nbsp;";
  		s+="收款方全称：<input type=\"text\" style=\"width:300px;height:20px;\" class=\"validate[required] text_new\" name=\"costpay_" + cost_pay_count + "\" id=\"costpay_" + cost_pay_count + "\" onfocus=\"javascript:getSupplierName(this);\">&nbsp;&nbsp;";
  	}else{
  		//s+="收款方全称：<input type=\"text\" style=\"width:100px;height:20px;\" class=\"validate[required]\" name=\"costpay_" + cost_pay_count + "\" id=\"costpay_" + cost_pay_count + "\" onkeydown=\"javascript:supplier_auto(this);\" width=\"300\">&nbsp;&nbsp;";
  		s+="收款方全称：<input type=\"text\" style=\"width:300px;height:20px;\" class=\"validate[required] text_new\" name=\"costpay_" + cost_pay_count + "\" id=\"costpay_" + cost_pay_count + "\" onfocus=\"javascript:getSupplierName(this);\">&nbsp;&nbsp;";
  	}
  	s+="时间：<input type=\"text\" onclick=\"WdatePicker();\" style=\"width:100px;\" class=\"validate[required] text Wdate\" name=\"costpaytime_" + cost_pay_count + "\" id=\"costpaytime_" + cost_pay_count + "\">&nbsp;&nbsp;";
    s+="金额：<input type=\"text\" onblur=\"tjcostpayment(this)\" style=\"width:100px; text-align:right;height:20px;\" class=\"validate[required,custom[money]]\" name=\"costpayamount_" + cost_pay_count + "\" id=\"costpayamount_" + cost_pay_count + "\"> 元&nbsp;&nbsp;";
    s+="收票类型：<select class=\"select\" name=\"costpaytype_" + cost_pay_count + "\" ><option value=1>广告</option><option value=2>服务</option></select>&nbsp;&nbsp;";
	s+="<img src=\"../images/close.png\" onclick=\"delcostpayment(this,'" + cost_pay_count + "')\" width=\"17\" height=\"17\" /><br /></div>";

	$("#costpaymentinfolist").append(s);
	var costpaycount = $("#costpaycount").val();
	$("#costpaycount").val(costpaycount + cost_pay_count + ",");
	if(cy_on){
		$("#copycostpaycount").val($("#costpaycount").val());
	}
	cost_pay_count++;
}

function delcostpayment(v,cost_pay_count){ 
	if(window.confirm("删除记录需重新填写拆月数据，确认？")){
		var newsuppliers = [];
		  var id = "costpay_" + cost_pay_count;
		  for(var i=0;i<suppliers.length;i++){
			  if(suppliers[i] != $("#" + id).val()){
				  newsuppliers.push(suppliers[i]);
			  } 
		  }
		  suppliers = newsuppliers;
		  
	  $(v).parent().remove();
	  
	  var amount=0;
	  $("#costpaymentinfolist").children().each(function(index, element) {
	    amount+=Number($(this).children().eq(2).val());
	  });
	  
	  $("#costpayment").text(amount.toFixed(2));
	  
	  var costpaycount = $("#costpaycount").val();
	  $("#costpaycount").val(costpaycount.replace("," + cost_pay_count + ",",","));
	  $("#copycostpaycount").val($("#costpaycount").val());
	  
	  
	  var tid = "table_" + cost_pay_count;
	  $("#" + tid).remove();
	  if($("#costpaycount").val() == ","){
		  $(".scbox") .html("");
		  $("#cy_json").val("");
	  }
	  //$("#cyform").submit();
	}
}

// 删除一条记录 只限第一层
function del(v){ 
  $(v).parent().remove();
}

// 如果执行单类型不为结算的话，开始时间必须是今天开始
//2013-12-20去除，按照开始时间与创建时间判断是否后补的执行单
function checkdatetype(){
	//var val = $('input:radio[name="exetype"]:checked').val();
	//if (val==3){
		WdatePicker({dateFmt:'yyyy-MM-dd'});
	//}else{ 
	//	WdatePicker({minDate:'%y-%M-%d'});
	//}
}

function addactor(){
	var name=$("#actorlist").find("option:selected").text();
  
  if (name=="请选择人员") {
	  return;
  }else{
	  /*
	  var has_val = false;
	  for(var i=0;i<actor_array.length;i++){
		  if(actor_array[i] == name){
			  has_val = true;
			  break;
		  } 
	  }
	  if(!has_val){
		  actor_array.push(name); 
	  }
  }
  $("#actor").val(actor_array.join(","));
  do_show();
  */
	  
	 var tmps = $("#actor").val();
	  if (tmps=="") {
		  var all_tmps = name;
	  }else{
		  tmps = tmps.replace(/,/g,"|");
		  tmps = tmps.replace(/，/g,"|");
		  tmps = tmps.split("|");
		  var ahas = false;
		  for(var i=0;i<tmps.length;i++){
			  if(tmps[i] == name){
				  ahas = true;
				  break;
			  } 
		  }
		  if(!ahas){
			  var all_tmps = tmps.join(",") + "," + name;
		  }else{
			  var all_tmps = tmps.join(",")
		  }
	  }
	 }
	 $("#actor").val(all_tmps);
}

function array_del(arr,n){
	if(n<0){
		return arr;
	}
	return arr.slice(0,n).concat(arr.slice(n+1,arr.length));
}

function do_show(){
	$("#actor").val(actor_array.join(","));
	var actor_show = "";
	  for(var i=0;i<actor_array.length;i++){
		  actor_show += actor_array[i] + "<img src=\"" + base_url + "images/close.png\" onclick=\"actor_del(" + i + ")\"/>";
		  if(i != actor_array.length - 1){
			  actor_show += " ";
		  }
	  }
	  $("#actor_show").html(actor_show);
}

function actor_del(n){
	actor_array = array_del(actor_array,n);
	do_show();
}


// 统计约定付款信息的总金额
function tjamount(v){
  if(isNaN(v.value)){
	  return;
  }else{
		 var val = v.value;
		 if(val.indexOf(".")!=-1){
			 val = Number(val);
			 v.value = val.toFixed(2); 
		 }	 
	 }
  var amount=0;
  $("#paytimelist").children().each(function(index, element) {
    amount+=Number($(this).children().eq(1).val());
  });
  
  $("#amount").text(amount.toFixed(2));
}

// 统计成本明细总金额
function tjcost(v){
	 if(isNaN(v.value)){
		 v.value = 0;
		  return;
	 }else{
		 var val = v.value;
		 if(val.indexOf(".")!=-1){
			 val = Number(val);
			 v.value = val.toFixed(2); 
		 }	 
	 }
	 
	var amount=0;
	$("#costinfolist").children().each(function(index, element) {
		amount+=Number($(this).children().eq(1).val());
	});
  
	$("#cost").text(amount.toFixed(2));
}

// 统计成本支付明细总金额
function tjcostpayment(v){
	if($.trim(v.value)==""){
		v.value = 0;
	}
	if(isNaN(v.value)){
		v.value = 0;
		return;
	}else{
		 var val = v.value;
		 if(val.indexOf(".")!=-1){
			 val = Number(val);
			 v.value = val.toFixed(2); 
		 }	 
	 }
	var amount=0;
	$("#costpaymentinfolist").children().each(function(index, element) {
		amount+=Number($(this).children().eq(2).val());
	});
  
	$("#costpayment").text(amount.toFixed(2));
}

// 检验左右两个数据是否一样
function checkdifferent(){
	$(".sbd1 tr").each(function(index, element) {
		if ($(this).children().eq(2).html()==null) return true;
		if ($(this).children().eq(1).html()!=$(this).children().eq(2).html()) $(this).addClass("bw1"); 
	});
}

function getcategory(obj){
	var id = obj.id;
	id = id.split("_");
	$.ajax({
		   type: "POST",
		   url: "do.php",
		   cache:false,
		   data: "action=getcategory&supplier=" + obj.value + "&t=" + Math.random() + "&vcode=" + vcode,
		   dataType:'text',
		   async: false,
		   success: function(msg){
			  	var xid = "costpaycategory_" + id[1];
			   	$("#" + xid + " option").remove();
				$("#" + xid).append(msg);
		   },
	 	   error: function(e){
	 		   alert("获取供应商产品分类出错");
	 	   }
	});
}

function cost_input(){
	$('[id^=cost_amount_]').blur(function(){
		var val = $(this).val();
		var id = $(this).attr("id");
		var kn = id.split("_");
		if(isNaN(val)){
			$(this).val("0");
			return;
		}else{
			 if(val.indexOf(".")!=-1){
				 val = Number(val);
				 $(this).val(val.toFixed(2)) ; 
			 }	 
		}
		var x = 0;
		var y = 0;
		$('[id^=cost_amount_]').each(function(){
			x += Number($(this).val());
		});
		$('[id^=cost_amount_' + kn[2] + ']').each(function(){
			y += Number($(this).val());
		});
		$("#coa").html(x.toFixed(2));
		$("#coa_" + kn[2]).html(y.toFixed(2));
	});
}

function re_cost_input(cyitem,cynum){
	var id = "cost_amount_" + cyitem;
	var kn = id.split("_");
	var val = $("#" + id).val();
	var x = 0;
	var y = 0;
	
	$('[id^=cost_amount_]').each(function(){
		x += Number($(this).val());
	});
	$('[id^=cost_amount_' + kn[2] + ']').each(function(){
		y += Number($(this).val());
	});
	$("#coa").html(x.toFixed(2));
	$("#coa_" + kn[2]).html(y.toFixed(2));
}