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
				<li class="on"><a>项目盈利分析</a></li>
				<li><a href="?o=effect&search=[SEARCH]">投放效果分析</a></li>
				<!-- <li><a href="?o=trend&search=[SEARCH]">投放趋势分析</a></li> -->
			</ul>
		</div>       
        <div class="listform fix">
        	<table width="100%" class="tabin">
              <tr>
				<td>
                	&nbsp; 关键字: <input id="search" style=" width:150px;height:20px;" value="[SEARCH]"  /> 
                    &nbsp; <input type="button" id="dosearch" value="搜 索" class="btn"/>
                </td>
              </tr>
            </table>
            </div>
                <div id="profit_gain_chartdiv" style="width: 100%; height: 600px; margin-bottom: 50px"></div>
                <div id="profit_cost_chartdiv" style="width: 100%; height: 600px;"></div>
            </div> 
        </div>
	</div>
</div>
<script type="text/javascript" src="[BASE_URL]script/jquery.min.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/jquery.tablesorter.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]js/nimads.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/jquery.sprintf.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]script/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]analyze/analyze.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]analyze/amcharts/amcharts.js" language="javascript"></script>
<script type="text/javascript" src="[BASE_URL]analyze/amcharts/serial.js" language="javascript"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
$(document).ready(function() {
	$("#dosearch").live("click",function () { dosearch(); });
});

function dosearch(){
  var starttime=$("#starttime").val();
  var endtime=$("#endtime").val();
  var searchcontent=$("#search").val();
  location.href=$.sprintf(base_url + "analyze/?o=profit&search=%s",searchcontent);
}

//  利润
    var profit_gain_chart;
    var profit_gain_key_json = [profit_gain_key_json];
    var profit_gain_json = [profit_gain_json];
    AmCharts.ready(function () {
        // SERIAL CHART
        profit_gain_chart = new AmCharts.AmSerialChart();
        profit_gain_chart.dataProvider = profit_gain_json;
        profit_gain_chart.categoryField = "year";
        profit_gain_chart.startDuration = 0.5;
        profit_gain_chart.balloon.color = "#000000";
        // AXES
        // category
        var categoryAxis = profit_gain_chart.categoryAxis;
        categoryAxis.fillAlpha = 1;
        categoryAxis.fillColor = "#FAFAFA";
        categoryAxis.gridAlpha = 0;
        categoryAxis.axisAlpha = 0;
        categoryAxis.gridPosition = "start";
        categoryAxis.position = "top";

        // value
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.title = "利润";
        valueAxis.dashLength = 5;
        valueAxis.axisAlpha = 0;
        valueAxis.minimum = [profit_gain_min];
        valueAxis.maximum = [profit_gain_max];
        valueAxis.integersOnly = false;
        valueAxis.gridCount = 10;
        valueAxis.reversed = false; // this line makes the value axis reversed
        profit_gain_chart.addValueAxis(valueAxis);

        for(var i in profit_gain_key_json){
            var graph = new AmCharts.AmGraph();
            graph.title = profit_gain_key_json[i];
            graph.valueField = profit_gain_key_json[i];
            graph.hidden = false; // this line makes the graph initially hidden
            graph.balloonText = profit_gain_key_json[i]+": [[category]]: [[value]]";
            graph.lineAlpha = 1;
            graph.bullet = "round";
            profit_gain_chart.addGraph(graph);
        }
        // CURSOR
        var chartCursor = new AmCharts.ChartCursor();
        chartCursor.cursorPosition = "mouse";
        chartCursor.zoomable = false;
        chartCursor.cursorAlpha = 0;
        profit_gain_chart.addChartCursor(chartCursor);

        // LEGEND
        var legend = new AmCharts.AmLegend();
        legend.useGraphSettings = true;
        profit_gain_chart.addLegend(legend);

        // WRITE
        profit_gain_chart.write("profit_gain_chartdiv");
    });


    //  当月执行成本金额
        var profit_cost_chart;
        var profit_cost_key_json = [profit_cost_key_json];
        var profit_cost_json = [profit_cost_json];
        AmCharts.ready(function () {
            // SERIAL CHART
            profit_cost_chart = new AmCharts.AmSerialChart();
            profit_cost_chart.dataProvider = profit_cost_json;
            profit_cost_chart.categoryField = "year";
            profit_cost_chart.startDuration = 0.5;
            profit_cost_chart.balloon.color = "#000000";
            // AXES
            // category
            var categoryAxis = profit_cost_chart.categoryAxis;
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
            valueAxis.minimum = [profit_cost_min];
            valueAxis.maximum = [profit_cost_max];
            valueAxis.integersOnly = false;
            valueAxis.gridCount = 10;
            valueAxis.reversed = false; // this line makes the value axis reversed
            profit_cost_chart.addValueAxis(valueAxis);

            for(var i in profit_cost_key_json){
                var graph = new AmCharts.AmGraph();
                graph.title = profit_cost_key_json[i];
                graph.valueField = profit_cost_key_json[i];
                graph.hidden = false; // this line makes the graph initially hidden
                graph.balloonText = profit_cost_key_json[i]+": [[category]]: [[value]]";
                graph.lineAlpha = 1;
                graph.bullet = "round";
                profit_cost_chart.addGraph(graph);
            }
            // CURSOR
            var chartCursor = new AmCharts.ChartCursor();
            chartCursor.cursorPosition = "mouse";
            chartCursor.zoomable = false;
            chartCursor.cursorAlpha = 0;
            profit_cost_chart.addChartCursor(chartCursor);

            // LEGEND
            var legend = new AmCharts.AmLegend();
            legend.useGraphSettings = true;
            profit_cost_chart.addLegend(legend);

            // WRITE
            profit_cost_chart.write("profit_cost_chartdiv");
        });
</script>
</body>
</html>