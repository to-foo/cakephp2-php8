<?php

App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_pie');
App::import('Vendor', 'jpgraph/jpgraph_pie3d');

/************************************************************************************************
 *																								*
 * Daten für Anzeige erzeugen																	*
 *																								*
 ************************************************************************************************/

if(count($Mistakes[key($Mistakes)]['Mistakes']['errors']) == 0) {
	die('no data to display');
} else {
	$legend = Hash::extract($Mistakes[key($Mistakes)]['Mistakes']['description'], '{n}');
	$values = Hash::extract($Mistakes[key($Mistakes)]['Mistakes']['errors'], '{n}');
	$labels = Hash::extract($Mistakes[key($Mistakes)]['Mistakes']['label'], '{n}');
}

if(isset($SearchingDescription) && is_array($SearchingDescription)){ 
	$Description = __('search criteria',true) . ":\n\n";
	foreach($SearchingDescription as $_key => $_data){
		foreach($_data as $__key => $__data){
			$Description .= $__data . "\n";
		}
	}
	
	$txt = new Text(trim($Description));
//	$txt->SetPos(950,15);
	$txt->SetPos(0.01, 0.05, 'left', 'top');
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
$GrahpHeigth = 700;
if(count($values) > 1 && count($values) <= 10) $GrahpHeigth = 500;
if(count($values) > 10 && count($values) <= 20) $GrahpHeigth = 500;
if(count($values) > 20 && count($values) <= 29) $GrahpHeigth = 1000;
if(count($values) > 30) $GrahpHeigth = 1250;
if(count($values) > 35) $GrahpHeigth = 1500;
if(count($values) > 40) $GrahpHeigth = 2000;
	
// Create the graph.
$graph = new PieGraph(max($ImageWidth * .95, 1300),$GrahpHeigth,'auto');
$graph->img->SetMargin(20,20,5,20);
$graph->ClearTheme();

$plot = new PiePlot(($values));
$plot->SetLegends(($legend));
$plot->SetLabels(($labels));
$plot->SetStartAngle(45);
$plot->SetCenter(0.25,0.56);
$plot->SetLabelPos(5);
$plot->SetGuideLines(true,false);
$plot->SetGuideLinesAdjust(2.25);
$plot->SetSize(0.25);	
//$plot->ExplodeAll(20);
//$plot->value->SetColor('white');
$graph->Add($plot);
$graph->legend->Pos(0.01, 0.01, 'right', 'top');
$graph->title->Set(__('Overview',true) . ' ' . __('from',true) . ' ' . $PeriodoFTime[0] . ' ' . __('to',true) . ' ' . $PeriodoFTime[1] . ' (' . __('creation date of the testing reports',true) . ')');
$graph->title->SetAlign('left');
$graph->SetFrame(false);

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
