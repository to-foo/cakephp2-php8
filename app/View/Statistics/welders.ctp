<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_bar');

$welders = $data['extra']['welders'];
$datas = array('total' => array(),'erfüllt' => array(),'nicht erfüllt' => array(),'nicht ausgewertet' => array());
$max = 0;

foreach($welders as $_key => $_welders){
	$datas['total'][] = $_welders['all'];
	$datas['erfüllt'][] = $_welders['e'];
	$datas['nicht erfüllt'][] = $_welders['ne'];
	$datas['nicht ausgewertet'][] = $_welders['-'];
}

if(count($datas['total']) > 0){
	$max = max($datas['total']);
} else {
	return;
}

/************************************************************************************************
 *																								*
 * Daten f�r Anzeige erzeugen																		*
 *																								*
 ************************************************************************************************/

// Barplot-Daten erzeugen und maximalwert für Graphen ermitteln

//ksort($welders);
/************************************************************************************************
 * 																								*
 * Den Graph erzeugen																			*
 * 																								*
 ************************************************************************************************/

$theme = new UniversalTheme();

if(count($datas['total']) > 50){
	echo '<div class="hint"><P>';
	echo __('Das Diagramm wird nicht angezeigt, da zu viele Diagrammbalken dargestellt werden müssten.',true);
	echo '</p></div>';
	return;
}

$barcolors = array(
			'total'=>'#61a9f3',	#blue
            'erfüllt'=>'#61E3A9',		#green
            'nicht erfüllt'=>'#f381b9',	#red
            'nicht ausgewertet'=>'gray');

// Create the graph.
$graph = new Graph(max($data['extra']['width']*.95, 800), max($data['extra']['height'], 600));
$graph->SetScale('textint', 0, $max);
$graph->img->SetMargin(20,20,5,5);
$graph->ClearTheme();

// X-Achsen-Beschreibungen erzeugen
$graph->xaxis->SetTickLabels(array_keys($welders));

// Graphen aus Bardaten erzeugen
$plot = array();
foreach($datas as $type => $data) {
	$plot[$type] = new BarPlot(array_values($data));
	$theme->ApplyPlot($plot[$type]);
	$plot[$type]->SetLegend(__('Weld type '.$type, true));
	$plot[$type]->SetFillColor($barcolors[$type]);
	$plot[$type]->SetColor('lightgray');
	$plot[$type]->value->SetFormat('%u');
	$plot[$type]->value->setAngle(90);
	$plot[$type]->value->show();
	$plot[$type]->value->HideZero();
	$plot[$type]->SetWidth(20);
}

$graph->Add(new GroupBarPlot(array_values($plot)));

// Setup graph layout
$theme->SetupGraph($graph);

$graph->xaxis->SetLabelAngle(90);
$graph->yaxis->SetTitle(__('No. of welds', true), 'middle');

$graph->legend->SetPos(0.025, 0.05, 'right', 'top');
$graph->legend->SetLayout(LEGEND_VERT);
$graph->legend->SetFillColor('#e3e3e3');

$graph->legend->SetShadow('#acacac', 1);

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
