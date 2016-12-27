//上传
function up_uploadfile(v,attacheid,only_excel,only_one){
	if($(v).prev().val()=="") { 
		alert("请选择要上传的文件！"); 
		return; 
	}
 
	//得到上传文件的ID 
	var fileid=$(v).prev().attr("id");
  
	$(v).attr("disabled","disabled");
	$(v).css("width","100px");
	$(v).attr("value","正在上传.....");
  
	$.ajaxFileUpload({
		url:base_url + "upload.php?fileid=" + fileid + "&only_excel=" + only_excel,
		secureuri:false,
		fileElementId:fileid,
		dataType: "text",
		error: function (data,status,e){ 
			alert(e);
			//alert("网络连接有误，请重试！！"); 
		},
		success: function (data, status){
			ss=data.split("|");
			if (ss[0]!=1) { 
				alert(ss[1]);
			}else{
				var dids = $("#" + attacheid).val();
				if(only_one == 0){
					//可上传多个文件
					$("#" + attacheid).val(dids + ss[1] + "^");
				}else{
					//只可上传一个文件
					$("#" + attacheid).val(ss[1]);
				}
				name=$.sprintf("<a href=\"" + base_url + "download.php?did=%s\" target=\"_blank\">%s</a> &nbsp;(%s)",ss[1],ss[2],ss[3]);
				if(only_one == 0){
					del=$.sprintf("<img src=\"" + base_url + "images/close.png\" onclick=\"up_del(this,'" + ss[1] + "','" + attacheid + "')\"/>");
				}else{
					del = "";
				}
				s=$.sprintf("<div did=%s id=\"%s_d\"> %s&nbsp;%s </div>",ss[1],ss[1],name,del);
				if(only_one == 1){
					var iid = dids + "_d";
					$("#" + iid).remove();
				}
				$(v).parent().after(s); 
			}
		}
	});
	$(v).attr("value","上传");
	$(v).removeAttr("disabled");
	$(v).removeAttr("style");
//  $(v).className("btn");
	$(v).attr("className","btn");
}

/*********************
删除上传附件
*********************/
function up_del(v,id,attacheid){
	var dids = $("#" + attacheid).val();
	$("#" + attacheid).val(dids.replace("^" + id + "^","^"));
	$(v).parent().remove();
}