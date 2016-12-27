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
	}).eq(menu_s).click();
});

function showpermission(v){
  var s="";
  var tmps=$(v).find("option:selected").text();
  var id=$(v).val();

  if (tmps.indexOf("(")<=0){
	  o="permissionlistbymodule"; 
  }else{
	  o="permissionlistbydep";
  }
  $.ajax({
	   type: "POST",
	   url: "do.php",
	   cache:"false",
	   data: "action=" + o + "&id=" + id + "&t=" + Math.random() + "&vcode=" + $("#vcode").val(),
	   dataType:'text',
	   async: false,
	   success: function(data){
		  $(v).next().empty();
		  $(v).next().next().empty();
		  $(v).next().append(data);
	   },
	   error: function(e){
		   alert("获取数据异常");
	   }
  });
}

//增加流程内容
function addprocesscontent()
{

  var s="<div>";
      s+="名称：<input type=\"text\" class=\"validate[required]\" name=\"pname_" + c + "\" style=\"height:20px;\"/> &nbsp;&nbsp;"; 
      s+="权限：<select onchange=\"showpermission(this)\" class=\"select\" style=\"width:200px\">"+ss+"</select>&nbsp;&nbsp;";
	  s+="<select onchange=\"setpermission(this)\" class=\"select\" style=\"width:200px\"><option value=\"\">请选择</option></select>&nbsp;&nbsp;";
	  s+="<input type=\"text\" class=\"validate[required]\"  readonly=\"readonly\" style=\"width:150px;height:20px;\" name=\"content_" + c + "\" /> &nbsp;&nbsp;"; 
      s+="<img src=\"../images/close.png\" onclick=\"del(this,'" + c + "')\" width=\"17\" height=\"17\" /><br /></div>";

  $("#processcontentlist").append(s);
  var now = $("#contents").val();
  $("#contents").val(now + c + ",");
  c +=1;
}

function setpermission(v)
{
  var tmps=$(v).find("option:selected").text();
  var id=$(v).val();
  if(id!=""){
	  tmps1=$.sprintf("%s^%s",tmps,id);
  }else{
	  tmps1 = "";
  }
  
  $(v).next().val(tmps1);
}

function del(v,id)
{ 
  $(v).parent().remove();
  var now = $("#contents").val();
  $("#contents").val(now.replace("," + id + ",",","));
}

/******************** depprocess  ***************/
//增加流程内容
function adddepprocesscontent()
{
  var s="<div>";
      s+="名称：<input type=\"text\" class=\"validate[required]\" name=\"pname_" + c + "\" style=\"height:20px;\"/> &nbsp;&nbsp;"; 
      s+="权限：<select onchange=\"showpermission(this)\" class=\"select\" style=\"width:200px\">"+ss+"</select>&nbsp;&nbsp;";
	  s+="<select onchange=\"setpermission(this)\" class=\"select\" style=\"width:200px\"><option value=\"\">请选择</option></select>&nbsp;&nbsp;";
	  s+="<input type=\"text\" class=\"validate[required]\" readonly=\"readonly\" style=\"width:150px;height:20px;\" name=\"content_" + c + "\"/> &nbsp;&nbsp;"; 
      s+="<img src=\"../images/close.png\" onclick=\"del(this,'" + c + "')\" width=\"17\" height=\"17\" /><br /></div>";

  $("#depprocesscontentlist").append(s);
  var now = $("#contents").val();
  $("#contents").val(now + c + ",");
  c +=1;
}


