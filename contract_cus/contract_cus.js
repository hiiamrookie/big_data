// 初始配置
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
	}).eq(menu_c).click();	
});


function fmkcid(){
  if ($("#isfmkcid").attr("checked")=="checked"){
	  $("#fmkcid").show();  
	  $("#fmkcid").addClass("validate[required]");
  }else{
	  $("#fmkcid").hide();
	  $("#fmkcid").removeClass("validate[required]");
  } 
}

function showtype(v){
	var n=$(v).val();
  
	if (n==2){
		$("#showfmkcid").show();
	}else{
		$("#showfmkcid").hide();
	}
}

function del(v)
{
  if (!confirm("确认删除此条内容？")) return; 
  $(v).parent().remove();
}

//增加保证金支付
function addbzj(){
	var s="<div>媒体：<input type=\"text\" style=\"width:100px;height:20px;\" name=\"bzjname_" + baozhengjin_count + "\" id=\"bzjname_" + baozhengjin_count + "\" class=\"validate[required]\" />";
    s+="&nbsp;";
    s+="比例：<input type=\"text\" style=\"width:30px;height:20px;\" name=\"bzjbl_" + baozhengjin_count + "\" id=\"bzjbl_" + baozhengjin_count + "\" class=\"validate[required,custom[number],min[0],max[100]]\" /> <b>%</b>";
    s+="&nbsp;";
    s+="金额：<input type=\"text\" class=\"validate[required,custom[number]]\" style=\"width:100px; text-align:right;height:20px;\" name=\"bzjamount_" +  baozhengjin_count + "\" id=\"bzjamount_" +  baozhengjin_count + "\" /> 元";
	s+="&nbsp;<img src=\"" + base_url + "images/close.png\" onclick=\"del_bzj(this," + baozhengjin_count + ")\" width=\"17\" height=\"17\" /><br /></div>";
	  
	$("#bzjlist").append(s);
	var baozhengjincount = $("#baozhengjincount").val();
	$("#baozhengjincount").val(baozhengjincount + baozhengjin_count + ",");
	baozhengjin_count++;
}

function del_bzj(obj,count){
	$(obj).parent().remove();
	var baozhengjincount = $("#baozhengjincount").val();
	$("#baozhengjincount").val(baozhengjincount.replace("," + count + ",",","));	
}

//点击增加广告主信息，增加一个输入框
function adddaili(){
	var s = "<div> 代理商：<input type=\"text\" style=\"width:200px;height:20px;\" class=\"validate[required]\" name=\"dailishang_" + daili_count + "\" id=\"dailishang_" + daili_count + "\"/> &nbsp; ";
	s += "广告主： <input type=\"text\" style=\"width:100px;height:20px;\" class=\"validate[required]\" name=\"guanggaozhu_" + daili_count + "\" id=\"guanggaozhu_" + daili_count + "\"/> &nbsp;";
	s += "<img src=\"" + base_url + "images/close.png\" onclick=\"del_daili(this," + daili_count + ")\" width=\"17\" height=\"17\" /></div>";
	$("#daililist").append(s); 
	var dailicount = $("#dailicount").val();
	$("#dailicount").val(dailicount + daili_count + ",");
	daili_count++;
}

function del_daili(obj,count){
	 $(obj).parent().remove();
	 var dailicount = $("#dailicount").val();
	 $("#dailicount").val(dailicount.replace("," + count + ",",","));
}

//根据直客代理商的选择，显示是否添加广告主信息
function showdaili()
{
  var n=$("input[name='type1']:checked").val();
  
  if (n==2) $("#showdaili").show();
  else $("#showdaili").hide();
}

//增加媒体投放的拆分
function addcf1(){ 
	var s="<div>";
	s+="媒体：<input type=\"text\" style=\"width:100px;height:20px;\" class=\"validate[required]\" name=\"media_" + mediatf_count + "\" id=\"media_" + mediatf_count + "\"/>&nbsp;&nbsp;";
	s+="金额：<input type=\"text\" style=\"width:80px;height:20px;\" name=\"mediaamount_" + mediatf_count + "\" id=\"mediaamount_" + mediatf_count + "\"/>&nbsp;&nbsp;";
	s+="广告形式及优惠政策：<input type=\"text\" style=\"width:300px;height:20px;\" name=\"advformat_" + mediatf_count + "\" id=\"advformat_" + mediatf_count + "\"/>&nbsp;&nbsp;";
	s+="<img src=\"" + base_url + "images/close.png\" onclick=\"del_media(this," + mediatf_count + ")\" width=\"17\" height=\"17\" /></div>";
	$("#cflist1").append(s); 
	var mediatfcount = $("#mediatfcount").val();
	$("#mediatfcount").val(mediatfcount + mediatf_count + ",");
	mediatf_count++;
}

function del_media(obj,count){
	$(obj).parent().remove();
	var mediatfcount = $("#mediatfcount").val();
	$("#mediatfcount").val(mediatfcount.replace("," + count + ",",","));
}
//增加服务内容的拆分
function addcf2(){ 
	var s="<div>";
	s+="<select name=\"cftype_" + service_count + "\" id=\"cftype_" + service_count + "\" class=\"select\">";
	s+=fw_amount_options;
	s+="</select>&nbsp;&nbsp;";
	s+="金额：<input type=\"text\" style=\"height:20px;\" width=\"100\" name=\"serviceamount_" + service_count + "\" id=\"serviceamount_" + service_count + "\"/>&nbsp;&nbsp;";
	s+="<img src=\"" + base_url + "images/close.png\" onclick=\"del_service(this," + service_count + ")\" width=\"17\" height=\"17\" /></div>";

	$("#cflist2").append(s);
	var servicecount = $("#servicecount").val();
	$("#servicecount").val(servicecount + service_count + ",");
	service_count++;
}

function del_service(obj,count){
	$(obj).parent().remove();
	var servicecount = $("#servicecount").val();
	$("#servicecount").val(servicecount.replace("," + count + ",",","));
}

//合同状态未归档理由的显示
function showfilestatus(v)
{
  if ($(v).val()==1) $("#showfilereason").hide();
  else $("#showfilereason").show();
}
