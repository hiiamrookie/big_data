$(document).ready(function() {

   var $calendar = $('#calendar');
   var type=$("h1").attr("id");

   $calendar.weekCalendar({
      timeslotsPerHour : 6,
      allowCalEventOverlap : false,
      overlapEventsSeparate: false,
      firstDayOfWeek : 1,
      businessHours :{start: 8, end: 18, limitDisplay: false },
      daysToShow : 7,
      height : function($calendar) {
         return $(window).height() - $("h1").outerHeight() - 1;
      },
      eventRender : function(calEvent, $event) {
         if (calEvent.end.getTime() < new Date().getTime()) {
            $event.css("backgroundColor", "#aaa");
            $event.find(".wc-time").css({
               "backgroundColor" : "#999",
               "border" : "1px solid #888"
            });
         }
      },
      draggable : function(calEvent, $event) {
         return calEvent.readOnly != true;
      },
      resizable : function(calEvent, $event) {
         return calEvent.readOnly != true;
      },
      eventNew : function(calEvent, $event) {
         var $dialogContent = $("#event_edit_container");
         resetForm($dialogContent);
         var startField = $dialogContent.find("select[name='start']").val(calEvent.start);
         var endField = $dialogContent.find("select[name='end']").val(calEvent.end);
         var titleField = $dialogContent.find("input[name='title']");
         var bodyField = $dialogContent.find("textarea[name='body']");
         var is_tel_meetingField = $dialogContent.find("input[name='is_tel_meeting']");

         $dialogContent.dialog({
            modal: true,
            title: "新的预定",
            close: function() {
               $dialogContent.dialog("destroy");
               $dialogContent.hide();
               $('#calendar').weekCalendar("removeUnsavedEvents");
            },
            buttons: {
               "保存" : function() {
                  calEvent.start = new Date(startField.val());
                  calEvent.end = new Date(endField.val());
                  calEvent.title = titleField.val();
                  calEvent.telmeeting = is_tel_meetingField.attr("checked") == "checked" ? 1 : 0;
                  //calEvent.body = bodyField.val();
                  calEvent.telmeeting_type = 0;

				  if (calEvent.start=="") { alert("请选择开始时间"); return; }
				  if (calEvent.end=="") { alert("请选择结束时间"); return; }
				  if(is_tel_meetingField.attr("checked") == "checked"){
					  //选中
					  var isok = false;
					  $("input[name='tel_meeting_type']").each(function(){
						  if($(this).attr("checked") == "checked"){
							  isok = true; 
							  return;
						  }	 
					  });
					  if(!isok){
						  alert("请选择电话会议类型：两方或多方"); 
						  return;						  
					  }else{
						  calEvent.telmeeting_type = $("input[name='tel_meeting_type']:checked").val();
					  }
				  }
				  //if (calEvent.title=="") { alert("请输入会议标题"); return; }

				  $.ajax({
				  		type:"get", url:"do.php", cache:"false", dataType:"text",
						data:{ o:"add",start:calEvent.start.toUTCString() ,end:calEvent.end.toUTCString(),title:calEvent["title"],type:type,telmeeting_type:calEvent["telmeeting_type"],telmeeting:calEvent["telmeeting"],t:Math.random(),vcode:vcode },
						error: function (data,status,e){ alert(e);},
						success:function(data,text)
						{
							alert(data);
							location.reload();
						}
				  });
                  
               },
               "取消" : function() {
                  $dialogContent.dialog("close");
               }
            }
         }).show();

         $dialogContent.find(".date_holder").text($calendar.weekCalendar("formatDate", calEvent.start));
         setupStartAndEndTimeFields(startField, endField, calEvent, $calendar.weekCalendar("getTimeslotTimes", calEvent.start));

      },
      eventDrop : function(calEvent, $event) {
      },
      eventResize : function(calEvent, $event) {
      },
      eventClick : function(calEvent, $event) {

         if (calEvent.readOnly) {
            return;
         }

         var $dialogContent = $("#event_edit_container");
         resetForm($dialogContent);
         var startField = $dialogContent.find("select[name='start']").val(calEvent.start);
         var endField = $dialogContent.find("select[name='end']").val(calEvent.end);
		 var tt=calEvent.title.indexOf("<br>");
		 var tmps=calEvent.title.substr(tt+5, calEvent.title.indexOf("》")-tt-4);
		 var tmps1=calEvent.title.substr(0,tt);
         var titleField = $dialogContent.find("input[name='title']").val(tmps);
         var bodyField = $dialogContent.find("textarea[name='body']");
         //bodyField.val(calEvent.body);
         var is_tel_meetingField = $dialogContent.find("input[name='is_tel_meeting']").attr("checked",calEvent.telmeeting);
         $("input[name='tel_meeting_type']").each(function(){
			  $(this).attr("disabled",!calEvent.telmeeting) ;
			  if(this.value == calEvent.telmeeting_type){
				  $(this).attr("checked",true) ;
			  }
		  });

         $dialogContent.dialog({
            modal: true,
            title: "撤销 - " + tmps1,//calEvent.title,
            close: function() {
               $dialogContent.dialog("destroy");
               $dialogContent.hide();
               $('#calendar').weekCalendar("removeUnsavedEvents");
            },
            buttons: {
				/*
               "保存" : function() {
				  if (startField.val()=="") { alert("请选择开始时间"); return; }
				  if (endField.val()=="") { alert("请选择结束时间"); return; }
				  calEvent.start = new Date(startField.val());
                  calEvent.end = new Date(endField.val());
                  calEvent.title = titleField.val();
                  calEvent.body = bodyField.val();
				  
				  if (calEvent.title=="") { alert("请输入会议标题"); return; }

				  $.ajax({
				  		type:"get", url:"do.php", cache:"false", dataType:"text",
						data:{ o:"edit",id:calEvent.id,start:startField.val() ,end:endField.val(),title:calEvent["title"],type:type,t:Math.random() },
						error: function (data,status,e){ alert(e);},
						success:function(data,text)
						{
						  if (data==1) { alert("您选择的时间段已被预订或有冲突，请重新选择"); return; }
						  alert("修改成功");
						  location.reload();
						  //$calendar.weekCalendar("removeUnsavedEvents");
						  //$calendar.weekCalendar("updateEvent", calEvent);
                  		  //$dialogContent.dialog("close");
						}
				  });
               },
			   */
               "撤销预订" : function() {
				   $.ajax({
				  		type:"get", url:"do.php", cache:"false", dataType:"text",
						data:{ o:"del",id:calEvent.id,t:Math.random(),vcode:vcode },
						error: function (data,status,e){ alert(e);},
						success:function(data,text)
						{
						  alert(data);
						  location.reload();
						}
				  });
                  //$calendar.weekCalendar("removeEvent", calEvent.id);
                  //$dialogContent.dialog("close");
               },
               "取消" : function() {
                  $dialogContent.dialog("close");
               }
            }
         }).show();

         var startField = $dialogContent.find("select[name='start']").val(calEvent.start);
         var endField = $dialogContent.find("select[name='end']").val(calEvent.end);
         $dialogContent.find(".date_holder").text($calendar.weekCalendar("formatDate", calEvent.start));
         setupStartAndEndTimeFields(startField, endField, calEvent, $calendar.weekCalendar("getTimeslotTimes", calEvent.start));
         $(window).resize().resize(); //fixes a bug in modal overlay size ??

      },
      eventMouseover : function(calEvent, $event) {
      },
      eventMouseout : function(calEvent, $event) {
      },
      noEvents : function() {
      },
      //data : function(start, end, callback) {
      //   callback(getEventData());
      //}
	  data : "index.php?o=json&type="+type+"&t="+Math.random()
   });
   
   //$calendar.weekCalendar("refresh");

   function resetForm($dialogContent) {
      $dialogContent.find(":text").val("");
      $dialogContent.find("textarea").val("");
      $dialogContent.find("checkbox").attr("checked",false);
      $dialogContent.find(":radio").each(function(){
    	  $(this).attr("checked",false);
    	  $(this).attr("disabled",true);
      });
   }

   function getEventData() {
      var year = new Date().getFullYear();
      var month = new Date().getMonth();
      var day = new Date().getDate();

      return {
         events : [
            {
               "id":1,
               "start": new Date(year, month, day, 12),
               "end": new Date(year, month, day, 13, 30),
               "title":"Lunch with Mike",
			   "body":"asafsfsdfasdfsdfa"
            },
            {
               "id":2,
               "start": new Date(year, month, day, 14),
               "end": new Date(year, month, day, 14, 45),
               "title":"Dev Meeting"
            },
            {
               "id":3,
               "start": new Date(year, month, day + 1, 17),
               "end": new Date(year, month, day + 1, 17, 45),
               "title":"Hair cut"
            },
            {
               "id":4,
               "start": new Date(year, month, day - 1, 8),
               "end": new Date(year, month, day - 1, 9, 30),
               "title":"Team breakfast"
            },
            {
               "id":5,
               "start": new Date(year, month, day + 1, 14),
               "end": new Date(year, month, day + 1, 15),
               "title":"Product showcase"
            },
            {
               "id":6,
               "start": new Date(year, month, day, 10),
               "end": new Date(year, month, day, 11),
               "title":"I'm read-only",
               readOnly : true
            }

         ]
      };
   }


   /*
    * Sets up the start and end time fields in the calendar event
    * form for editing based on the calendar event being edited
    */
   function setupStartAndEndTimeFields($startTimeField, $endTimeField, calEvent, timeslotTimes) {

      for (var i = 0; i < timeslotTimes.length; i++) {
         var startTime = timeslotTimes[i].start;
         var endTime = timeslotTimes[i].end;
         var startSelected = "";
         if (startTime.getTime() === calEvent.start.getTime()) {
            startSelected = "selected=\"selected\"";
         }
         var endSelected = "";
         if (endTime.getTime() === calEvent.end.getTime()) {
            endSelected = "selected=\"selected\"";
         }
         $startTimeField.append("<option value=\"" + startTime + "\" " + startSelected + ">" + timeslotTimes[i].startFormatted + "</option>");
         $endTimeField.append("<option value=\"" + endTime + "\" " + endSelected + ">" + timeslotTimes[i].endFormatted + "</option>");

      }
      $endTimeOptions = $endTimeField.find("option");
      $startTimeField.trigger("change");
   }

   var $endTimeField = $("select[name='end']");
   var $endTimeOptions = $endTimeField.find("option");

   //reduces the end time options to be only after the start time options.
   $("select[name='start']").change(function() {
      var startTime = $(this).find(":selected").val();
      var currentEndTime = $endTimeField.find("option:selected").val();
      $endTimeField.html(
            $endTimeOptions.filter(function() {
               return startTime < $(this).val();
            })
            );

      var endTimeSelected = false;
      $endTimeField.find("option").each(function() {
         if ($(this).val() === currentEndTime) {
            $(this).attr("selected", "selected");
            endTimeSelected = true;
            return false;
         }
      });

      if (!endTimeSelected) {
         //automatically select an end date 2 slots away.
         $endTimeField.find("option:eq(1)").attr("selected", "selected");
      }

   });

});
