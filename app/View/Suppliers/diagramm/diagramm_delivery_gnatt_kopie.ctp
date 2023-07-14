<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_gantt');


// Setup a basic Gantt graph
$graph = new GanttGraph(2000,0);
$graph->SetMarginColor('gray:1.7');
$graph->SetColor('white');

// Setup the graph title and title font
$graph->title->Set("Übersicht Verzug von allen Lieferungen aufgeteilt nach Equipmenttype");
$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);
$graph->SetDateRange('2018-12-01','2020-12-31');
// Show three headers
$graph->ShowHeaders(GANTT_HMONTH | GANTT_HYEAR);
// Set the column headers and font
$graph->scale->actinfo->SetColTitles( array('Equipment','Erste Lieferung','Letzte Lieferung',"Durchschnittlicher\nVerzug"),array(100));
$graph->scale->actinfo->SetFont(FF_ARIAL,FS_BOLD,11);
$graph->scale->divider->SetWeight(3);
//pr($data);

$Start = $Delivery['Plan']['Start'];
$End = $Delivery['Ist']['End'];

$bar = new GanttBar(0,array('TRM','',''),$Start->format('Y-m-d'),$End->format('Y-m-d'),'',0.35);
// For each group make the name bold but keep the dates as the default font
$bar->title->SetColumnFonts(array(array(FF_ARIAL,FS_BOLD,11)));

// Add group markers
$bar->leftMark->SetType( MARK_LEFTTRIANGLE );
$bar->leftMark->Show();
$bar->rightMark->SetType( MARK_RIGHTTRIANGLE );
$bar->rightMark->Show();
$bar->SetFillColor('black');
$bar->SetPattern(BAND_SOLID,'black');
$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
$graph->Add($bar);

$Start = $Delivery['Plan']['Start'];
$End = $Delivery['Plan']['End'];

$range = $Delivery['TimeRangePlan']->days;
$days = $Delivery['TimeRangeStart']->days;

$bar = new GanttBar(1,array('geplantes Lieferdatum',$Start->format('Y-m-d'),$End->format('Y-m-d'),' '),$Start->format('Y-m-d'),$End->format('Y-m-d'),'',0.45);
$bar->SetPattern(BAND_RDIAG,'black');
$bar->SetFillColor('red');
$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
$graph->Add($bar);

$Start = $Delivery['Ist']['Start'];
$End = $Delivery['Ist']['End'];

$range = $Delivery['TimeRangeIst']->days;
$days = $Delivery['TimeRangeEnd']->days;
$bar = new GanttBar(2,array('erfolgtes Lieferdatum',$Start->format('Y-m-d'),$End->format('Y-m-d'),strval($AverageDelayAll['result']) . ' Tage'),$Start->format('Y-m-d'),$End->format('Y-m-d'),'',0.45);
$bar->SetPattern(BAND_RDIAG,'black');
$bar->SetFillColor('blue');
$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
$graph->Add($bar);
$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
$graph->Add($bar);

$datanew = array();
$x = 5;
foreach($DataForPlot['TRM'] as $key => $value) {
//pr($value['AverageDelay']['result']);

	if($value['DeliveryDetail']['Ist']['Start'] < $value['DeliveryDetail']['Plan']['Start'] === true){
		$Start = $value['DeliveryDetail']['Ist']['Start'];
	} else {
		$Start = $value['DeliveryDetail']['Plan']['Start'];
	}
	if($value['DeliveryDetail']['Plan']['End'] > $value['DeliveryDetail']['Ist']['End'] === true){
		$End = $value['DeliveryDetail']['Plan']['End'];
	}	else {
		$End = $value['DeliveryDetail']['Ist']['End'];
	}

	if($Start->format('Y') > 2021) continue;

//	$datanew[] = array($key,$Start->format('Y-m-d'),$End->format('Y-m-d'));
	$bar = new GanttBar($x,array($key,'',''),$Start->format('Y-m-d'),$End->format('Y-m-d'),'',0.35);
	// For each group make the name bold but keep the dates as the default font
	$bar->title->SetColumnFonts(array(array(FF_ARIAL,FS_BOLD,11)));

	// Add group markers
	$bar->leftMark->SetType( MARK_LEFTTRIANGLE );
	$bar->leftMark->Show();
	$bar->rightMark->SetType( MARK_RIGHTTRIANGLE );
	$bar->rightMark->Show();
	$bar->SetFillColor('black');
	$bar->SetPattern(BAND_SOLID,'black');
	$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
	$graph->Add($bar);

	$Start = $value['DeliveryDetail']['Plan']['Start'];
	$End = $value['DeliveryDetail']['Plan']['End'];

	$range = $value['DeliveryDetail']['TimeRangePlan']->days;
	$days = $value['DeliveryDetail']['TimeRangeStart']->days;

	$x++;
	$bar = new GanttBar($x,array('geplant',$Start->format('Y-m-d'),$End->format('Y-m-d'),' '),$Start->format('Y-m-d'),$End->format('Y-m-d'),'',0.45);
	$bar->SetPattern(BAND_RDIAG,'black');
	$bar->SetFillColor('orange');
	$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
	$graph->Add($bar);

	$Start = $value['DeliveryDetail']['Ist']['Start'];
	$End = $value['DeliveryDetail']['Ist']['End'];

	$range = $value['DeliveryDetail']['TimeRangeIst']->days;
	$days = $value['DeliveryDetail']['TimeRangeEnd']->days;

	$x++;
	$bar = new GanttBar($x,array('erfolgt',$Start->format('Y-m-d'),$End->format('Y-m-d'),strval($value['AverageDelay']['result']) . ' Tage'),$Start->format('Y-m-d'),$End->format('Y-m-d'),'',0.45);
	$bar->SetPattern(BAND_RDIAG,'black');
	$bar->SetFillColor('green');
	$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
	$graph->Add($bar);
	$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
	$graph->Add($bar);

	$x++;
}

//die();

if(isset($return) && $return === true) {
	$data = $graph->Stroke(_IMG_HANDLER);
	ob_start();
	imagepng($data);
	$data = ob_get_contents();
	ob_end_clean();
	echo $data;
} else {
	$graph->Stroke();
}
die();
?>
