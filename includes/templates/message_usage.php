<?php 
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

wetext_chkAuth();
	if(isset($_SESSION["SESLETSGO"])){
		unset($_SESSION["SESLETSGO"]);
	}	
	include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'header.php';		
?>

<script>
	var chart;	
	var chartData = [
		{
			"x-axis": "Available: <?php echo esc_html($response_val[0]['totalAvailable']);?>",
			"value": <?php echo esc_html($response_val[0]['totalAvailable']);?>,
			"color": "#1BA8E1"
		},		
		{
			"x-axis": "Incoming: <?php echo esc_html($response_val[0]['incomingSMS']); ?>",
			"value": <?php echo esc_html($response_val[0]['incomingSMS']); ?>,
			"color": "#000000"
		},
		{
			"x-axis": "Outgoing(<?php echo esc_html($response_val[0]['totalOutgoing']);?>) SMS(<?php echo esc_html($response_val[0]['outgoingSMS']); ?>) MMS(<?php echo esc_html($response_val[0]['outgoingMMS']); ?>) - Total: <?php echo esc_html($response_val[0]['totalOutgoing']); ?>",
			"value": <?php echo esc_html($response_val[0]['totalOutgoing']); ?>,
			"color": "#000000"
		},
		{
			"x-axis": "Emails(FREE): <?php echo esc_html($response_val[0]['outgoingEmails']); ?>",
			"value": <?php echo esc_html($response_val[0]['outgoingEmails']); ?>,
			"color": "#000000"
		},
	];

	AmCharts.ready(function () {
		// SERIAL CHART
		chart = new AmCharts.AmSerialChart();
		chart.dataProvider = chartData;
		chart.categoryField = "x-axis";
		chart.startDuration = 1;		

		// AXES
		// category		
		var categoryAxis = chart.categoryAxis;
		categoryAxis.labelRotation = 45; // this line makes category values to be rotated
		categoryAxis.gridAlpha = 0;
		categoryAxis.fillAlpha = 1;
		categoryAxis.fillColor = "#FFFFFF";
		categoryAxis.gridPosition = "start";

		// value		
		var valueAxis = new AmCharts.ValueAxis();
		valueAxis.dashLength = 0;
		valueAxis.title = "";
		valueAxis.axisAlpha = 0;
		chart.addValueAxis(valueAxis);

		// GRAPH		
		var graph = new AmCharts.AmGraph();
		graph.valueField = "value";
		graph.colorField = "color";
		graph.balloonText = "<b>[[category]]</b>";
		graph.type = "column";
		graph.lineAlpha = 0;
		graph.fillAlphas = 1;
		graph.fixedColumnWidth = 100;
		chart.addGraph(graph);

		// CURSOR		
		var chartCursor = new AmCharts.ChartCursor();
		chartCursor.cursorAlpha = 0;
		chartCursor.zoomable = false;
		chartCursor.categoryBalloonEnabled = false;
		chart.addChartCursor(chartCursor);
		chart.creditsPosition = "top-right";

		// WRITE		
		chart.write("chartdiv");
	});
</script>

<div class="stapsTabs">
	<ol class="cd-multi-tabs text-bottom">
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=admin_profile');?>">Admin Profile</a></li>
		<li class="active_tab"><a href="<?php echo esc_url_raw($base_path.'&wtab=message_usage');?>">Message Usage</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=manage_api');?>">Manage API</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=configuration');?>">Configuration</a></li>                           
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=invite_people');?>">Invite People</a></li>
	</ol>
</div>
<table class="form-table">
<tbody>
<tr>
<td>
	<div id="chartdiv" style="width: 90%; height: 400px;"></div>
</td>
</tr>
</tbody>
</table>