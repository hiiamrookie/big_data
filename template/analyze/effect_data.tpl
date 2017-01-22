<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> 我的执行单</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<link href="[BASE_URL]css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="[BASE_URL]css/tablesorter.css" rel="stylesheet" media="screen" type="text/css" />
<link rel="shortcut icon" href="[BASE_URL]favicon.ico" type="image/x-icon"/>
</head>
<body>
[LEFT]
<div id="main">
	<div class="nav_top">[TOP]</div>
	<div id="content" class="fix">
		<div class="crumbs">数据报表 - 执行成本明细</div>
		<div class="tab" id="tab" style="height:30px">
            <ul>
                <li><a href="?o=profit&search=[SEARCH]">项目盈利分析</a></li>
                <li class="on"><a>投放效果分析</a></li>
                <!-- <li><a href="?o=trend&search=[SEARCH]">投放趋势分析</a></li> -->
            </ul>
		</div>       
        <div class="listform fix">
        	<table width="100%" class="tabin">
              <tr>
				<td>
                	&nbsp; 关键字: <input id="search" style=" width:150px;height:20px;" value="[SEARCH]"  /> 
                    &nbsp; 开始时间：<input type="text" onfocus="WdatePicker()" id="starttime" style=" width:80px;height:20px;" value="[STARTTIME]" />
                    &nbsp; 结束时间：<input type="text" onfocus="WdatePicker()" id="endtime" style=" width:80px;height:20px;" value="[ENDTIME]" />
                    &nbsp; <select id='range'  style=" width:100px;height:20px;">
                                <option value ="" >时间范围</option>
                                <option value ="hour">24时</option>
                                <option value ="day">日报</option>
                                <option value="month">月报</option>
                                <option value="year">年报</option>
                            </select>
                    &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
                </td>
              </tr>
            </table>
            </div>
            	<div id="chartdiv1" style="width: 100%; height: 400px;"></div>
                <div id="chartdiv2" style="width: 100%; height: 400px;"></div>
                <div id="chartdiv3" style="width: 100%; height: 400px;"></div>

                <div id="chartdiv4" style="width: 100%; height: 400px;"></div>
                <div id="chartdiv5" style="width: 100%; height: 400px;"></div>
                <div id="chartdiv6" style="width: 100%; height: 400px;"></div>


                <div id="chartdiv7" style="width: 100%; height: 400px;"></div>
                <div id="chartdiv8" style="width: 100%; height: 400px;"></div>


            </div> 
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]report/report.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]report/amcharts/amcharts.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]report/amcharts/serial.js" language="javascript"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
$(document).ready(function() {
    var range = '[RANGE]';
	$("#dosearch").live("click",function () { dosearch(); });

    $('select>option').each(function(){
        var value = $(this).val();
        if(value == range){
            $(this).attr('selected',true);
        }else{
             $(this).attr('selected',false);
        }
    })
});

function dosearch(){
  var starttime=$("#starttime").val();
  var endtime=$("#endtime").val();
  var searchcontent=$("#search").val();
  var range = $("#range").val();

  location.href=$.sprintf(base_url + "analyze/?o=effect&starttime=%s&endtime=%s&search=%s&range=%s",starttime,endtime,searchcontent,range);
  //location.href=$.sprintf(base_url + "executive/?o=mylist&starttime=%s&endtime=%s&search=%s",starttime,endtime,searchcontent);
}

chart([order_cnt_data_json],'chartdiv1',"订单数  /  (单位/个)","order_cnt",get_max([order_cnt_data_json],'order_cnt'));
chart([reg_cnt_data_json],'chartdiv2',"注册量   /   (单位/个)","reg_cnt",get_max([reg_cnt_data_json],'reg_cnt'));
chart([order_amount_data_json],'chartdiv3',"订单金额   /   (单位/元)","order_amount",get_max([order_amount_data_json],'order_amount'));

chart([budget_data_json],'chartdiv4',"预算   /   (单位/元)","budget",get_max([budget_data_json],'budget'));
chart([dsp_impressions_data_json],'chartdiv5',"展示次数     /   (单位/次)","dsp_impressions",get_max([dsp_impressions_data_json],'dsp_impressions'));

//alert(get_max([dsp_cpc_data_json],'dsp_cpc'));
chart([dsp_cpc_data_json],'chartdiv6',"CPC   /   (单位/元)","dsp_cpc",get_max([dsp_cpc_data_json],'dsp_cpc'));
chart([dsp_cpm_data_json],'chartdiv7',"CPM   /   (单位/元)","dsp_cpm",get_max([dsp_cpm_data_json],'dsp_cpm'));

chart([dsp_ctr_data_json],'chartdiv8',"CTR   /   (单位/百分比)","dsp_ctr",100);



function chart(data,div,title,key,max){
    var chart;

    var chartData = data;

    AmCharts.ready(function () {
        // SERIAL CHART
        chart = new AmCharts.AmSerialChart();
        chart.dataProvider = chartData;
        chart.categoryField = "year";
        chart.startDuration = 0.5;
        chart.balloon.color = "#000000";

        // AXES
        // category
        var categoryAxis = chart.categoryAxis;
        categoryAxis.fillAlpha = 1;
        categoryAxis.fillColor = "#FAFAFA";
        categoryAxis.gridAlpha = 0;
        categoryAxis.axisAlpha = 0;
        categoryAxis.gridPosition = "start";
        categoryAxis.position = "top";

        // value
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.title = title;
        valueAxis.dashLength = 5;
        valueAxis.axisAlpha = 0;
        valueAxis.minimum = 0;
        valueAxis.maximum = max;
        valueAxis.integersOnly = true;
        valueAxis.gridCount = 10;
        valueAxis.reversed = false; // this line makes the value axis reversed
        chart.addValueAxis(valueAxis);

        // GRAPHS
        // Italy graph
        var graph = new AmCharts.AmGraph();
        graph.title = title;
        graph.valueField = key;
        graph.hidden = false; // this line makes the graph initially hidden
        if(key == 'dsp_ctr'){
             graph.balloonText = "[[category]]: [[value]]"+"%";
        }else{
            graph.balloonText = "[[category]]: [[value]]";
        }
       
        graph.lineAlpha = 1;
        graph.bullet = "round";
        chart.addGraph(graph);

        // CURSOR
        var chartCursor = new AmCharts.ChartCursor();
        chartCursor.cursorPosition = "mouse";
        chartCursor.zoomable = false;
        chartCursor.cursorAlpha = 0;
        chart.addChartCursor(chartCursor);

        // LEGEND
        var legend = new AmCharts.AmLegend();
        legend.useGraphSettings = true;
        chart.addLegend(legend);

        // WRITE
        chart.write(div);
    });
}


function get_max(json,key){
    var max = 1;
    for(var i in json){
        if(max<Math.ceil(json[i][key])){
            max = Math.ceil(json[i][key]);
        }
    }

    return max;
}
//  当月执行成本金额
/*    var chart1;
    var key_json = [key_json];
    var month_execute_amount_data_json = [month_execute_amount_data_json];
    AmCharts.ready(function () {
        // SERIAL CHART
        chart1 = new AmCharts.AmSerialChart();
        chart1.dataProvider = month_execute_amount_data_json;
        chart1.categoryField = "year";
        chart1.startDuration = 0.5;
        chart1.balloon.color = "#000000";
        // AXES
        // category
        var categoryAxis = chart1.categoryAxis;
        categoryAxis.fillAlpha = 1;
        categoryAxis.fillColor = "#FAFAFA";
        categoryAxis.gridAlpha = 0;
        categoryAxis.axisAlpha = 0;
        categoryAxis.gridPosition = "start";
        categoryAxis.position = "top";

        // value
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.title = "当月执行金额";
        valueAxis.dashLength = 5;
        valueAxis.axisAlpha = 0;
        valueAxis.minimum = [min];
        valueAxis.maximum = [max];
        valueAxis.integersOnly = false;
        valueAxis.gridCount = 10;
        valueAxis.reversed = false; // this line makes the value axis reversed
        chart1.addValueAxis(valueAxis);

        for(var i in key_json){
            var graph = new AmCharts.AmGraph();
            graph.title = key_json[i];
            graph.valueField = key_json[i];
            graph.hidden = false; // this line makes the graph initially hidden
            graph.balloonText = key_json[i]+": [[category]]: [[value]]";
            graph.lineAlpha = 1;
            graph.bullet = "round";
            chart1.addGraph(graph);
        }
        // CURSOR
        var chartCursor = new AmCharts.ChartCursor();
        chartCursor.cursorPosition = "mouse";
        chartCursor.zoomable = false;
        chartCursor.cursorAlpha = 0;
        chart1.addChartCursor(chartCursor);

        // LEGEND
        var legend = new AmCharts.AmLegend();
        legend.useGraphSettings = true;
        chart1.addLegend(legend);

        // WRITE
        chart1.write("chartdiv1");
    });*/

</script>
</body>
</html>