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
		<div class="crumbs">数据报表 - 应得返点</div>
		<div class="tab" id="tab" style="height:30px">
        	<ul>
                <li><a href="?o=data&search=[SEARCH]">执行成本明细</a></li>
                <li class="on"><a>应得返点</a></li>
                <li><a href="?o=profit&search=[SEARCH]">利润</a></li>
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
            	<div id="chartdiv1" style="width: 100%; height: 600px;"></div>
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
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
<script type="text/javascript">
var base_url = "[BASE_URL]";
$(document).ready(function() {
	$("#dosearch").live("click",function () { dosearch(); });
});

function dosearch(){
  var starttime=$("#starttime").val();
  var endtime=$("#endtime").val();
  var searchcontent=$("#search").val();
  location.href=$.sprintf(base_url + "report/?o=rebate&search=%s",searchcontent);
}

//  当月执行成本金额
    var chart1;
    var key_json = [key_json];
    var deserve_rebate_data_json = [deserve_rebate_data_json];
    AmCharts.ready(function () {
        // SERIAL CHART
        chart1 = new AmCharts.AmSerialChart();
        chart1.dataProvider = deserve_rebate_data_json;
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
        valueAxis.title = "应得返点";
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
    });

</script>
</body>
</html>