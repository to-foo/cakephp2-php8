<?php
if(!isset($data['Scheme']['AdvancesOrder'])) return false;
if(!isset($data['Status'])) return false;

App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_utils_inc');

$dateUtils = new DateScaleUtils();

// Setup a basic graph
$width=1000; $height=500;
$graph = new Graph($width, $height);

$grace = 86400;
$n = count($data['Status']['all_advance_methods']['Tickline']['timestamp']);
$xmin = $data['Status']['all_advance_methods']['Tickline']['timestamp'][0] - ($grace * 2);
$xmax = $data['Status']['all_advance_methods']['Tickline']['timestamp'][$n - 1] + $grace;

// We set the x-scale min/max values to avoid empty space
// on the side of the plot
$graph->SetMargin(60,20,40,60);
$graph->img->SetAntiAliasing(true);

$graph->SetScale('intlin',0,0,$xmin,$xmax);
$graph->yscale->SetAutoMax(100);
$graph->yaxis->scale->SetGrace(10);

// Setup the titles
$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,12);
$graph->title->Set('Prozentualer Fortschritt der angesetzten Überprüfungen');
//$graph->subtitle->SetFont(FF_ARIAL,FS_ITALIC,10);
//$graph->subtitle->Set('(Example using DateScaleUtils class)');

$graph->legend->SetPos(0.025, 0.05, 'right', 'top');
$graph->legend->SetLayout(LEGEND_VERT);
$graph->legend->SetFillColor('#ffff');
$graph->legend->SetShadow(0);
$graph->legend->SetFrameWeight(2);

// Get manual tick every second year
list($tickPos,$minTickPos) = $dateUtils->getTicks($data['Status']['all_advance_methods']['Tickline']['timestamp'],DSUTILS_DAY1);

$graph->xaxis->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
$graph->xaxis->SetLabelAngle(30);
$graph->xaxis->SetLabelFormatString('d.m.Y',true);
$graph->xaxis->SetTickPositions($tickPos,$minTickPos);

$graph->yaxis->title->Set('Prozent (%)');
// First add an area plot

foreach ($data['Status'] as $key => $value) {

  if($key == 'all_advance_methods') continue;

  $lp = new LinePlot($value['AdvancesLinePlot']['yaxis'],$value['AdvancesLinePlot']['xaxis']);
  $graph->Add($lp);
  $lp->SetWeight(4);
  $lp->SetLegend(__($key, true) . ' (' . $value['result']['advance_all_percent'] . ')');

}

/*
$lp1 = new LinePlot($data['Status']['ndt']['AdvancesLinePlot']['yaxis'],$data['Status']['ndt']['AdvancesLinePlot']['xaxis']);
$graph->Add($lp1);
$lp1->SetWeight(4);
$lp1->SetLegend(__('ndt', true));
$lp1->SetColor('orange');
*/
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
