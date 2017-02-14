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
<!--                 <div id="chartdiv6" style="width: 100%; height: 400px;"></div>


<div id="chartdiv7" style="width: 100%; height: 400px;"></div>
<div id="chartdiv8" style="width: 100%; height: 400px;"></div> -->


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
<script type="text/javascript" src="[BASE_URL]js/common.js" language="javascript"></script>
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

chart([order_cnt_data_json],'chartdiv1',"订单数  /  (单位：个)","order_cnt",get_max([order_cnt_data_json],'order_cnt'));
chart([reg_cnt_data_json],'chartdiv2',"注册量   /   (单位：个)","reg_cnt",get_max([reg_cnt_data_json],'reg_cnt'));
chart([order_amount_data_json],'chartdiv3',"订单金额   /   (单位：元)","order_amount",get_max([order_amount_data_json],'order_amount'));

chart([budget_data_json],'chartdiv4',"预算   /   (单位：元)","budget",get_max([budget_data_json],'budget'));
/*chart([dsp_impressions_data_json],'chartdiv5',"展示次数     /   (单位：次)","dsp_impressions",get_max([dsp_impressions_data_json],'dsp_impressions'));

//alert(get_max([dsp_cpc_data_json],'dsp_cpc'));
chart([dsp_cpc_data_json],'chartdiv6',"CPC   /   (单位：元)","dsp_cpc",get_max([dsp_cpc_data_json],'dsp_cpc'));
chart([dsp_cpm_data_json],'chartdiv7',"CPM   /   (单位：元)","dsp_cpm",get_max([dsp_cpm_data_json],'dsp_cpm'));

chart([dsp_ctr_data_json],'chartdiv8',"CTR   /   (单位：%)","dsp_ctr",100);*/



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



//click;impress;cpc;cpm;ctr;

var chart;
var chartData = [];

AmCharts.ready(function () {
    // generate some random data first
    generateChartData();

    // SERIAL CHART
    chart = new AmCharts.AmSerialChart();

    chart.dataProvider = chartData;
    chart.categoryField = "date";

    // listen for "dataUpdated" event (fired when chart is inited) and call zoomChart method when it happens
    chart.addListener("dataUpdated", zoomChart);

    chart.synchronizeGrid = true; // this makes all axes grid to be at the same intervals

    // AXES
    // category
    var categoryAxis = chart.categoryAxis;
    categoryAxis.parseDates = false; // as our data is date-based, we set parseDates to true
    categoryAxis.minPeriod = "DD"; // our data is daily, so we set minPeriod to DD
    categoryAxis.minorGridEnabled = true;
    categoryAxis.axisColor = "#DADADA";
    categoryAxis.twoLineMode = true;
    categoryAxis.dateFormats = [{
         period: 'fff',
         format: 'JJ:NN:SS'
     }, {
         period: 'ss',
         format: 'JJ:NN:SS'
     }, {
         period: 'mm',
         format: 'JJ:NN'
     }, {
         period: 'hh',
         format: 'JJ:NN'
     }, {
         period: 'DD',
         format: 'DD'
     }, {
         period: 'WW',
         format: 'DD'
     }, {
         period: 'MM',
         format: 'MMM'
     }, {
         period: 'YYYY',
         format: 'YYYY'
     }];

    // first value axis (on the left)
    var valueAxis1 = new AmCharts.ValueAxis();
    valueAxis1.axisColor = "#FF6600";
    valueAxis1.axisThickness = 2;
    chart.addValueAxis(valueAxis1);

    // second value axis (on the right)
    var valueAxis2 = new AmCharts.ValueAxis();
    valueAxis2.position = "right"; // this line makes the axis to appear on the right
    valueAxis2.axisColor = "#FCD202";
    valueAxis2.gridAlpha = 0;
    valueAxis2.axisThickness = 2;
    chart.addValueAxis(valueAxis2);

    // third value axis (on the left, detached)
    var valueAxis3 = new AmCharts.ValueAxis();
    valueAxis3.offset = 60; // this line makes the axis to appear detached from plot area
    valueAxis3.gridAlpha = 0;
    valueAxis3.axisColor = "#B0DE09";
    valueAxis3.axisThickness = 2;
    chart.addValueAxis(valueAxis3);

    // four value axis (on the right)
    var valueAxis4 = new AmCharts.ValueAxis();
    valueAxis4.offset = 120; // this line makes the axis to appear detached from plot area
    valueAxis4.axisColor = "#424242";
    valueAxis4.axisThickness = 2;
    chart.addValueAxis(valueAxis4);

    // five value axis (on the right)
    var valueAxis5 = new AmCharts.ValueAxis();
    valueAxis5.position = "right"; // this line makes the axis to appear on the right
    valueAxis5.offset = 60; 
    valueAxis5.axisColor = "#FF0000";
    valueAxis5.gridAlpha = 0;
    valueAxis5.axisThickness = 2;
    chart.addValueAxis(valueAxis5);

    // GRAPHS
    // first graph
    var graph1 = new AmCharts.AmGraph();
    graph1.valueAxis = valueAxis1; // we have to indicate which value axis should be used
    graph1.title = "impressions";
    graph1.valueField = "dsp_impressions";
    graph1.bullet = "round";
    graph1.hideBulletsCount = 50;
    graph1.bulletBorderThickness = 1;
    chart.addGraph(graph1);

    // second graph
    var graph2 = new AmCharts.AmGraph();
    graph2.valueAxis = valueAxis2; // we have to indicate which value axis should be used
    graph2.title = "click";
    graph2.valueField = "dsp_click";
    graph2.bullet = "square";
    graph2.hideBulletsCount = 30;
    graph2.bulletBorderThickness = 1;
    chart.addGraph(graph2);

    // third graph
    var graph3 = new AmCharts.AmGraph();
    graph3.valueAxis = valueAxis3; // we have to indicate which value axis should be used
    graph3.valueField = "dsp_cpc";
    graph3.title = "cpc";
    graph3.bullet = "triangleUp";
    graph3.hideBulletsCount = 30;
    graph3.bulletBorderThickness = 1;
    chart.addGraph(graph3);

    // four graph
    var graph4 = new AmCharts.AmGraph();
    graph4.valueAxis = valueAxis4; // we have to indicate which value axis should be used
    graph4.valueField = "dsp_cpm";
    graph4.title = "cpm";
    graph4.bullet = "triangleUp";
    graph4.hideBulletsCount = 30;
    graph4.bulletBorderThickness = 1;
    chart.addGraph(graph4);

    // five graph
    var graph5 = new AmCharts.AmGraph();
    graph5.valueAxis = valueAxis5; // we have to indicate which value axis should be used
    graph5.valueField = "dsp_ctr";
    graph5.title = "ctr";
    graph5.bullet = "triangleUp";
    graph5.hideBulletsCount = 30;
    graph5.bulletBorderThickness = 1;
    chart.addGraph(graph5);


    // CURSOR
    var chartCursor = new AmCharts.ChartCursor();
    chartCursor.cursorAlpha = 0.1;
    chartCursor.fullWidth = true;
    chartCursor.valueLineBalloonEnabled = true;
    chart.addChartCursor(chartCursor);

    // SCROLLBAR
    var chartScrollbar = new AmCharts.ChartScrollbar();
    chart.addChartScrollbar(chartScrollbar);

    // LEGEND
    var legend = new AmCharts.AmLegend();
    legend.marginLeft = 110;
    legend.useGraphSettings = true;
    chart.addLegend(legend);

    // WRITE
    chart.write("chartdiv5");
});

// generate some random data, quite different range
function generateChartData() {
    var dsp_total_json = [dsp_total_data_json];
    var firstDate = new Date();
    firstDate.setDate(firstDate.getDate() - 50);

    for(var i in dsp_total_json){
        //alert(dsp_total_json[i].dsp_impressions);
        var newDate = new Date(firstDate);
        newDate.setDate(newDate.getDate() + i);

        chartData.push({
          date: dsp_total_json[i].year,
          dsp_impressions: dsp_total_json[i].dsp_impressions,
          dsp_click: dsp_total_json[i].dsp_click,
          dsp_cpc: dsp_total_json[i].dsp_cpc,
          dsp_cpm: dsp_total_json[i].dsp_cpm,
          dsp_ctr: dsp_total_json[i].dsp_ctr
        });  
    }
}

// this method is called when chart is first inited as we listen for "dataUpdated" event
function zoomChart() {
    // different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
    chart.zoomToIndexes(0, 23);
}
</script>
</body>
</html>