<?php
//if(!isset($data['Scheme']['AdvancesOrder'])) return false;

App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_date');
//App::import('Vendor', 'jpgraph/jpgraph_utils_inc');

// Create the new graph
$graph = new Graph(800,500);

$graph->SetMargin(100,75,50,100);
$graph->img->SetAntiAliasing(true);

$graph->SetScale('datlin',$MinScale,$MaxScale);
$graph->title->Set($key . ' (C°)');

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetTimeAlign(MINADJ_1);

foreach ($data as $key => $value) {

	$line = new LinePlot($value['values']['y'],$xline);
	$graph->Add($line);

}

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
