<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_pie');
App::import('Vendor', 'jpgraph/jpgraph_pie3d');

/************************************************************************************************
 *																								*
 * Daten für Anzeige erzeugen																	*
 *																								*
 ************************************************************************************************/
$values = array_values($WelderOverview[key($WelderOverview)]);
$label = array(0 => __('erfüllte Prüfbereiche',true),1 => __('nicht erfüllte Prüfbereiche',true),2 => __('nicht ausgewertete Prüfbereiche',true));

if(isset($SearchingDescription) && is_array($SearchingDescription)){ 
	$Description = __('search criteria',true) . ":\n\n";
	foreach($SearchingDescription as $_key => $_data){
		foreach($_data as $__key => $__data){
			$Description .= $__data . "\n";
		}
	}
	
	$txt = new Text(trim($Description));
//	$txt->SetPos(950,15);
	$txt->SetPos(0.01, 0.15, 'left', 'top');
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
$GrahpHeigth = 400;
	
// Create the graph.
$graph = new PieGraph(max($ImageWidth * .95, 1300),$GrahpHeigth,'auto');
$graph->img->SetMargin(20,20,5,20);
$graph->ClearTheme();

$plot = new PiePlot3D(($values));
$plot->SetSliceColors(array('#038000','#ff0000','#636363'));
$plot->SetLegends(($label));
$plot->SetLabels(($values));
//$plot->SetStartAngle(80);
$plot->SetCenter(0.3,0.5);
$plot->SetLabelPos(1);
//$plot->SetGuideLines(true,false);
//$plot->SetGuideLinesAdjust(2.5);
$plot->SetSize(0.5);
$plot->value->HideZero();	
//$plot->ExplodeAll(20);
$graph->Add($plot);
$graph->legend->Pos(0.01, 0.01, 'right', 'top');
$graph->title->Set(__('Overview',true) . ' ' . __('from',true) . ' ' . $PeriodoFTime[0] . ' ' . __('to',true) . ' ' . $PeriodoFTime[1] . ' (' . __('creation date of the testing reports',true) . ')');
$graph->title->SetAlign('left');
$graph->SetFrame(false);
// Display the graph

if(isset($SearchingDescription)) $graph->AddText($txt);

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
