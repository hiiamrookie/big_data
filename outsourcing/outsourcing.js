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
	}).eq(menu_out).click();
});

//增加流程内容
function addprocesscontent(){
	var s="<div>";
	var id = "auditer_" + c;
	s+="审核人：<input type=\"text\" class=\"validate[required]\" name=\"" + id + "\" id=\"" + id + "\" style=\"height:20px;width:200px;\"/> &nbsp;&nbsp;"; 
	s+="<img src=\"../images/close.png\" onclick=\"del(this,'" + c + "')\" width=\"17\" height=\"17\" /><br /></div>";

	$("#processcontentlist").append(s);
	var now = $("#contents").val();
	$("#contents").val(now + c + ",");
	c +=1;

	$("#" + id).autocomplete(base_url + "hr/index.php?o=getuser", { width: 200, max: 50 });
}

function del(v,id)
{ 
  $(v).parent().remove();
  var now = $("#contents").val();
  $("#contents").val(now.replace("," + id + ",",","));
}