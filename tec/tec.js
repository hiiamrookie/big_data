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
	}).eq(menu_t).click();
});

function addprequirement(){
	var s="<div>";
	s+="<input type=\"text\" style=\"width:500px;\" class=\"validate[required,maxSize[1000]] text\" name=\"prequirement_" + prequirement_count + "\" id=\"prequirement_" + prequirement_count + "\">&nbsp;&nbsp;";
	s+="<img src=\"../images/close.png\" onclick=\"delprequirement(this,'" + prequirement_count + "')\" width=\"17\" height=\"17\" /><br /></div>";
	
	$("#prequirementlist").append(s);
	var prequirement = $("#prequirement").val();
	$("#prequirement").val(prequirement + prequirement_count + ",");
	prequirement_count++;
}

function delprequirement(v,prequirement_count){ 
	  $(v).parent().remove();
	  var prequirement = $("#prequirement").val();
	  $("#prequirement").val(prequirement.replace("," + prequirement_count + ",",","));
}

function checkdifferent(){
  $(".sbd1 tr").each(function(index, element) {
	if ($(this).children().eq(2).html()==null) return true;
    if ($(this).children().eq(1).html()!=$(this).children().eq(2).html()) $(this).addClass("bw1");
});
}