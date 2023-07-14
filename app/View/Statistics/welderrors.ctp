<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_pie');

/************************************************************************************************
 *																								*
 * Daten für Anzeige erzeugen																	*
 *																								*
 ************************************************************************************************/



if(!isset($data['extra']['welderrors']['ne']) || !count($data['extra']['welderrors']['ne'])) {
	if(isset($return) && $return === true) {
		echo "false";
	} else {
		echo __('No displayable data');
	}
} else {

$labels = Hash::extract($data['extra']['welderrors']['ne'], '{n}.code');
$values = Hash::extract($data['extra']['welderrors']['ne'], '{n}.value');

// Wenn keine NE-Fehler vorhanden sind, kommt nur undefiniert 0
// muss korrigiert werden, sonst error
if(count($values) == 1 && $values[0] < 1 ){
	$labels[0] = __('No displayable data',true);
	$values[0] = 1;
}

/*
print '<pre>';
print_r($labels);
print '</pre>';
print '<pre>';
print_r($values);
print '</pre>';
die('tot');
*/	
	/************************************************************************************************
	 * 																								*
	 * Den Graph erzeugen																			*
	 * 																								*
	 ************************************************************************************************/

	$theme = new UniversalTheme();
	
	// Create the graph.
	$graph = new PieGraph(max($data['extra']['width']*.95, 800), max($data['extra']['height'], 600));
	$graph->img->SetMargin(20,20,5,5);

	$graph->ClearTheme();
	$plot = new PiePlot(($values));
	$plot->SetLegends(($labels));
	$plot->SetStartAngle(90);
	$plot->SetCenter(0.4, 0.5);
	$plot->SetGuideLines();
	
	$graph->Add($plot);
	$graph->legend->Pos(0.01, 0.01, 'right', 'top');

	$graph->title->Set(__('This diagram only contain ne-welds',true));
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
}