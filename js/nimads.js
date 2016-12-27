$(document).ready(function() {
  $("#SYS_Wdate").text(showdate());
  
  $("#tablelist tr").live("mouseover",function () { $(this).addClass("bw"); });
  $("#tablelist tr").live("mouseout",function () {$(this).removeClass("bw"); });
  
  $("#adduploadfile").live("click",function(){up_showfile(this);});
  
});

//显示系统日期
function showdate() 
{
  var now=new Date();
  var year = now.getFullYear();
  var month = now.getMonth()+1;
  var day = now.getDate();
  var dayname;
  
  if (now.getDay() == 0) dayname="星期日";
  if (now.getDay() == 1) dayname="星期一";
  if (now.getDay() == 2) dayname="星期二";
  if (now.getDay() == 3) dayname="星期三";
  if (now.getDay() == 4) dayname="星期四";
  if (now.getDay() == 5) dayname="星期五";
  if (now.getDay() == 6) dayname="星期六";
  
  return year+"年"+month+"月"+day+"日"+" "+dayname;
}
