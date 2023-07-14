<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_bar');
App::import('Vendor', 'jpgraph/jpgraph_table');

/************************************************************************************************
 *																								*
 * Daten für Anzeige erzeugen																		*
 *																								*
 ************************************************************************************************/
// Anzahl der Tage im Monat feststellen
// Template für Bars erzeugen, damit jeder vorkommende Tag des Monat überall zumindest mit 0 vertreten ist

$data = array();

$max = 0;
//$tickLabels = $Months;
$datas = array('e' => array(),'ne' => array(),'n' => array());

foreach($WelderOverview as $_key => $_days){
		$tickLabels[] = $_key;
		$datas['e'][] = $_days['e'];
		$datas['ne'][] = $_days['ne'];
		$datas['n'][] = $_days['non'];
}

/************************************************************************************************
 * 																								*
 * Suchbeschreibung erzeugen																			*
 * 																								*
 ************************************************************************************************/

if(isset($SearchingDescription) && is_array($SearchingDescription)){ 
	$Description = __('search criteria',true) . ":\n\n";
	foreach($SearchingDescription as $_key => $_data){
		foreach($_data as $__key => $__data){
			$Description .= $__data . "\n";
		}
	}
	
	$txt = new Text($Description);
	$txt->SetPos(10,30);
	$txt->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
	$txt->ParagraphAlign('left');
	$txt->SetBox('#fffff','#3c5bbe','gray',0,0);
	$txt->SetColor('#000000');
}

/************************************************************************************************
 * 																								*
 * Den Graph erzeugen																			*
 * 																								*
 ************************************************************************************************/

$theme = new UniversalTheme();


$barcolors = array(
            'e'=>'#81bf7f',		#green
            'ne'=>'#ff7f7f',	#red
            'n'=>'#b1b1b1');

// Create the graph.
$graph = new Graph(max($ImageWidth * .95, 1300), max($ImageHeight, 700));
$graph->SetScale('textlin', 0, $max);
$graph->ClearTheme();
$graph->img->SetMargin(50,20,170,80);

$graph->xaxis->SetTickLabels(array_values($tickLabels));
$graph->xaxis->SetLabelAngle(45);
// Graphen aus Bardaten erzeugen
$plot = array();
foreach($datas as $type => $data) {
	$plot[$type] = new BarPlot(array_values($data));
	$theme->ApplyPlot($plot[$type]);
	$plot[$type]->SetLegend(__('Weld type '.$type, true));
	$plot[$type]->SetFillColor($barcolors[$type]);
	$plot[$type]->SetColor('lightgray');
	$plot[$type]->value->SetFormat('%u');
	$plot[$type]->value->setFont(FF_FONT1,FS_NORMAL, 7);
	$plot[$type]->value->setAngle(90);
	$plot[$type]->value->HideZero();
	$plot[$type]->value->show();
}

$graph->Add(new GroupBarPlot(array_values($plot)));
$max = 30;
$yrange = range(0, $max);
$yrangeVal = array_unique(array_merge(array_map('round', range(0, 100, 100/$max), array_fill(0, $max, 0)), array(100)));

$_steps = array_merge(array(0), array_map('end', array_chunk($yrange, max(1,count($yrange)/10))));
$_stepVal = array_merge(array(0), array_map('end', array_chunk($yrangeVal, max(1,count($yrangeVal)/10))));

// Add the plot to the graph
$graph->SetY2OrderBack(false);
$graph->SetY2Scale('int', 0, 100);

// Setup graph layout
$theme->SetupGraph($graph);

$graph->yaxis->scale->SetGrace(5);
//$graph->yaxis->SetTitle(__('Percent', true), 'middle');

$graph->y2axis->scale->SetGrace(5);
$graph->y2axis->Hide();

$graph->legend->SetPos(0.025, 0.05, 'right', 'top');
$graph->legend->SetLayout(LEGEND_VERT);
$graph->legend->SetFillColor('#ffff');
$graph->legend->SetShadow(0);
$graph->legend->SetFrameWeight(2);

//$graph->title->Set(__(date('F', $month_ts)).' '.date('Y', $month_ts));
$graph->title->Set(__('Overview',true) . ' ' . __('from',true) . ' ' . $PeriodoFTime[0] . ' ' . __('to',true) . ' ' . $PeriodoFTime[1] . ' (' . __('creation date of the testing reports',true) . ')');
$graph->title->SetAlign('left');

if(isset($SearchingDescription)) $graph->AddText($txt);

// Display the graph
if(isset($return) && $return === true) {
	$data = $graph->Stroke( _IMG_HANDLER );
	ob_start();
	imagepng($data);
	$data = ob_get_contents();
	ob_end_clean();
	echo $data;
} else {
	// Display the graph
	$graph->Stroke();
}
?>
