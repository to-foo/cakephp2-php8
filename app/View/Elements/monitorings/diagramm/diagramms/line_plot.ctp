<?php
//if(!isset($data['Scheme']['AdvancesOrder'])) return false;

App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_date');
//App::import('Vendor', 'jpgraph/jpgraph_utils_inc');

// Create the new graph
$graph = new Graph(800,500);

$graph->SetMargin(50,50,100,100);
$graph->img->SetAntiAliasing(true);

$graph->SetScale('datlin',$MinScale,$MaxScale);
$graph->title->Set($key);

// Set the angle for the labels to 90 degrees
$graph->xaxis->SetLabelAngle(90);

// The automatic format string for dates can be overridden
//$graph->xaxis->scale->SetDateFormat('H:i:s');

// Adjust the start/end to a specific alignment
$graph->xaxis->scale->SetTimeAlign(MINADJ_1);

$line = new LinePlot($data['values']['y'],$data['values']['x']);
//$line->SetLegend('Legende');
//$line->SetFillColor('lightblue@0.5');
$graph->Add($line);


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

?>
