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
	}).eq(menu_f).click();
});

var pat = /^(([0]{1})|([1-9]{1}[0-9]*))?([\.]([0-9]{1,2}))?$/;
function common_check(val,israte){
	if(isNaN(val)){
		return "0";
	}else if(!pat.test(val)){
		return "0";
	}else if(israte && (val>100 || val<0)){
		return "0";
	}else{
		return val;
	}
}

function check_rate(obj){
	obj.value = common_check(obj.value,true);
	count_tax_sum();
}

function check_amount(obj){
	obj.value = common_check(obj.value,false);
	count_tax_sum();
}

function count_tax_sum(){
	var rate = Number($("#tax_rate").val());
	var amount = Number($("#amount").val());
	var tax = amount * rate / 100;
	$("#taxshow").html(tax.toFixed(2));
	var sumamount = (amount * (100 + rate)) / 100;
	$("#sumamountshow").html(sumamount.toFixed(2));
}