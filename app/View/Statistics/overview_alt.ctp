<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_bar');

/************************************************************************************************
 *																								*
 * Daten f�r Anzeige erzeugen																		*
 *																								*
 ************************************************************************************************/
// Anzahl der Tage im Monat feststellen
// Template für Bars erzeugen, damit jeder vorkommende Tag des Monat überall zumindest mit 0 vertreten ist
$tpl = $this->request->data['extra']['monts_all'];
$max = 0;

$day = array();
$tickLabels = array();
$datas = array('total' => array(),'e' => array(),'ne' => array(),'n' => array());
$dots = array();
foreach($this->request->data['extra']['welds']['statistics']['day'] as $_key => $_day){
	$date = strtotime(str_replace('.','-',$_key));
	$day[$date] = $_day;
	$day[$date]['date'] = $_key;
	$tickLabels[$date] = $_key;	
}

foreach($day as $_key => $_days){
	$datas['total'][] = $_days['all'];
	$datas['e'][] = $_days['e'];
	$datas['ne'][] = $_days['ne'];
	$datas['n'][] = $_days['-'];
	$dots[] = $_days['ne'];
}

$max = max($datas['total']);

/************************************************************************************************
 * 																								*
 * Den Graph erzeugen																			*
 * 																								*
 ************************************************************************************************/

$theme = new UniversalTheme();

if(count($datas['total']) > 40){
	echo '<div class="hint"><P>';
	echo __('Das Diagramm wird nicht angezeigt, da zu viele Diagrammbalken dargestellt werden müssten.',true);
	echo '</p></div>';
	return;
}

$barcolors = array(
			'total'=>'#61a9f3',	#blue
            'e'=>'#61E3A9',		#green
            'ne'=>'#f381b9',	#red
            'n'=>'gray');

// Create the graph.
$graph = new Graph(max($data['extra']['width']*.95, 800), max($data['extra']['height'], 600));
$graph->SetScale('textlin', 0, $max);
$graph->img->SetMargin(20,20,5,5);
$graph->ClearTheme();

$graph->xaxis->SetTickLabels(array_values($tickLabels));

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

$yrange = range(0, $max);
$yrangeVal = array_unique(array_merge(array_map('round', range(0, 100, 100/$max), array_fill(0, $max, 0)), array(100)));

$_steps = array_merge(array(0), array_map('end', array_chunk($yrange, max(1,count($yrange)/10))));
$_stepVal = array_merge(array(0), array_map('end', array_chunk($yrangeVal, max(1,count($yrangeVal)/10))));

// Create the linear plot
$lineplot=new LinePlot(array_values($dots));
$lineplot->SetLegend(__('Weld type ne', true).' in '.__('Percent', true));
$lineplot->SetColor("orange");
$lineplot->SetBarCenter();
$lineplot->value->SetFormat('%.1f%%');
$lineplot->value->HideZero();
$lineplot->value->SetAlign('center');
$lineplot->value->SetColor('darkred');
$lineplot->value->show(false);
$lineplot->mark->SetType(MARK_FILLEDCIRCLE, '', 2);
$lineplot->mark->SetColor('black');
$lineplot->mark->SetFillColor('red');
$lineplot->mark->Show();

// Add the plot to the graph
$graph->SetY2OrderBack(false);
$graph->SetY2Scale('int', 0, 100);
$graph->AddY2($lineplot);

// Setup graph layout
$theme->SetupGraph($graph);

$graph->yaxis->scale->SetGrace(5);
$graph->yaxis->SetTitle(__('Percent', true), 'middle');

$graph->y2axis->scale->SetGrace(5);
$graph->y2axis->Hide();

$graph->legend->SetPos(0.025, 0.05, 'right', 'top');
$graph->legend->SetLayout(LEGEND_VERT);
$graph->legend->SetFillColor('#e3e3e3');
$graph->legend->SetShadow('#acacac', 1);

//$graph->title->Set(__(date('F', $month_ts)).' '.date('Y', $month_ts));
$graph->title->Set('test');
$graph->title->SetAlign('left');


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
