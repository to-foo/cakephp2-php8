<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_date');
App::import('Vendor', 'jpgraph/jpgraph_utils_inc');

$datax = $data['xscale'];

$dateUtils = new DateScaleUtils();

list($tickPositions,$minTickPositions) = $dateUtils->GetTicks($datax);

$xmin = reset($datax);
$xmax = end($datax);

$graph = new Graph(2000,800);
$graph->SetScale('intlin',0,0,$xmin,$xmax);
$graph->SetBox(false);
$graph->SetMargin(75,100,75,200);
$graph->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,12);
$graph->title->Set('Zeitlicher Verlauf geplante und erfolgte Liefertermine');
$graph->img->SetAntiAliasing();

$graph->xaxis->SetPos('min');
$graph->xaxis->SetTickPositions($tickPositions,$minTickPositions);
$graph->xaxis->SetLabelFormatString('M Y',true);
$graph->xaxis->SetFont(FF_DV_SANSSERIF,FS_NORMAL,9);
$graph->xaxis->SetLabelAngle(45);
$graph->xgrid->Show();

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);
$graph->yaxis->SetTitle('Anzahl Equipments','middle');
$graph->yaxis->SetTitlemargin(40);

$p1 = new LinePlot($data['soll'],$datax);
$p1->SetLegend('Soll');
$p1->SetColor('teal');

$p2 = new LinePlot($data['ist'],$datax);
$p2->SetLegend('Ist');
$p2->SetColor('red');

$graph->Add($p1);
$graph->Add($p2);

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
