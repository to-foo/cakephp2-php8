<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_gantt');

$graph = new GanttGraph (1500,0);
$graph->SetShadow(0);

// Add title and subtitle
$graph->title->Set($headline);
$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->subtitle->Set($subheadline);

$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
$graph->SetMargin(30,50,30,30);

// Show day, week and month scale
$graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);

// Instead of week number show the date for the first day in the week
// on the week scale
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Make the week scale font smaller than the default
$graph->scale->week->SetFont(FF_FONT0);

// Use the short name of the month together with a 2 digit year
// on the month scale
$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
$graph->scale->month->SetFontColor("white");
$graph->scale->month->SetBackgroundColor("blue");
$graph->SetLabelVMarginFactor(1);
$graph->scale->divider->SetWeight(3);
$graph->scale->dividerh->SetWeight(3);

$x = 0;
foreach($developmentdatas['result'] as $_key => $_developmentdatas){
	// Format the bar for the first activity
	// ($row,$title,$startdate,$enddate)
	$prozent = $_developmentdatas['prozent'];
	$activity = new GanttBar($x,$_developmentdatas['name'],"2015-07-12","2015-08-22","[$prozent%]");
	$activity->progress->Set($_developmentdatas['dezimal']);
	$activity->progress->SetPattern(BAND_HVCROSS,"blue");

	// Yellow diagonal line pattern on a red background
	$activity->SetPattern(BAND_RDIAG,"yellow");
	$activity->SetFillColor("red");

	// Finally add the bar to the graph
	$graph->Add($activity);

	$x++;
}
// Display the graph
$graph->Stroke();
?>
