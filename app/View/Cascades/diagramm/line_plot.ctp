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

$Title = $key;
if(isset($data['measure'])) $Title .= ' (' . $data['measure'] . ')';

$graph->title->Set($Title);
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetTimeAlign(MINADJ_1);

$line = new LinePlot($data['values']['y'],$data['values']['x']);

$line->SetWeight(2);
$line->SetLegend($Title);

$graph->Add($line);
$graph->legend->SetFrameWeight(1);
$graph->legend->SetPos(0.1,0.95,'right','top');
$graph->xgrid->SetColor('#E3E3E3');

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
