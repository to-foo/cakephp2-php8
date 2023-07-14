<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_bar');
App::import('Vendor', 'jpgraph/jpgraph_table');

if(isset($DataForPlot[$Key]['BarplotData']) && count($DataForPlot[$Key]['BarplotData']) > 0) {
//	$PlotCount = count($DataForPlot[$Key]['BarplotData']['total']);
//	$PlotHightCount = $DataForPlot[$Key]['MaxEquipments'];
}
else return false;

$tickLabels = array();
$Font = 'FF_FONT1';

$nbrbar = 6;
$cellwidth = 50;
$tableypos = 50;
$tablexpos = 5;
$tablewidth = $nbrbar*$cellwidth;
$rightmargin = 30;

$table = new GTextTable();
$table->Set($TableData);
$table->SetPos($tablexpos,$tableypos+1);

// Basic table formatting
$table->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
$table->SetAlign('right');
$table->SetMinColWidth($cellwidth);

$table->SetRowFillColor(0,'#aacceb@0.7');
$table->SetRowFont(0,FF_DV_SANSSERIF,FS_BOLD,8);
$table->SetRowAlign(0,'center');
$table->SetBorder(1,'gray');

$theme = new UniversalTheme();

$graph = new Graph($ImageDimension['ImageWidth'], $ImageDimension['ImageHight'], 'auto');
$graph->ClearTheme();

$graph->img->SetMargin(10,10,230,75);
$graph->SetScale("textlin");
$graph->yaxis->scale->SetGrace(10);
$graph->SetMarginColor(10,10,10,10);

$graph->title->Set($Headline);
$graph->title->SetAlign('left');
$graph->title->setFont(FF_DV_SANSSERIF,FS_NORMAL,14);

$plot = array();
//$graph->SetScale('textlin', 0, $DataForPlot[$Key]['MaxEquipments'] + 2);
$graph->xaxis->SetTickLabels($DataForPlot[$Key]['TickLabels']);


foreach($DataForPlot[$Key]['BarplotData'] as $_key => $_data) {

//	pr(($_data));
	$plot[$_key] = new BarPlot(array_values($_data));
	$theme->ApplyPlot($plot[$_key]);
	$plot[$_key]->SetLegend(__($_key, true));
	$plot[$_key]->SetFillColor($barcolors[$_key]);
	$plot[$_key]->SetColor('lightgray');
	$plot[$_key]->value->SetFormat('%u');
	$plot[$_key]->value->setFont(FF_DV_SANSSERIF,FS_NORMAL, 7);
	$plot[$_key]->value->setAngle(90);
	$plot[$_key]->value->show();
}

$graph->Add(new GroupBarPlot(array_values($plot)));

$theme->SetupGraph($graph);

$graph->legend->SetFrameWeight(1);
$graph->legend->SetColumns(6);
$graph->legend->SetPos(0.5,0.98,'center','bottom');

if(count($TableData) > 0) $graph->Add($table);

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
