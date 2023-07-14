<?php
//if(!isset($data['Scheme']['AdvancesOrder'])) return false;

App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_odo');

$graph = new OdoGraph(800,500);
/*
// Setup graph titles
$graph->title->Set('Custom formatting');
$graph->title->SetColor('white');
$graph->title->SetFont(FF_ARIAL,FS_BOLD);

// Add drop shadow for graph
$graph->SetShadow();

// Now we need to create an odometer to add to the graph.
$odo = new Odometer();
$odo->SetColor("lightgray:1.9");

// Setup the scale
$odo->scale->Set(0,10);
$odo->scale->SetTicks(1,1);
$odo->scale->SetTickColor('brown');
$odo->scale->SetTickLength(0.05);
$odo->scale->SetTickWeight(2);

$odo->scale->SetLabelPos(0.8);
$odo->scale->label->SetFont(FF_FONT1, FS_BOLD);
$odo->scale->label->SetColor('brown');
$odo->scale->label->SetFont(FF_ARIAL,FS_NORMAL,10);

// Setup a label with a degree mark
$odo->scale->SetLabelFormat('%d bar');

// Set display value for the odometer
$odo->needle->Set(8);

// Add drop shadow for needle
$odo->needle->SetShadow();

// Add the odometer to the graph
$graph->Add($odo);
*/

$graph->title->Set($data['id'] . "\n" . round($OdoData,2) . " bar");
$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,20);
$graph->SetMarginColor("#ffffff");
$graph->SetColor("#ffffff");
$graph->SetFrame(false);
//$graph->caption->SetFont(FF_DV_SANSSERIF,FS_BOLD,20);
//$graph->caption->Set(round($data,2));
$graph->img->SetAntiAliasing(true);

$odo = new Odometer(ODO_HALF);

$odo->SetCenterAreaWidth(0.35);
$odo->AddIndication(0,8.5,"#038000");
$odo->AddIndication(8.5,9,"#ff7e00");
$odo->AddIndication(9,10,"#ff0000");
$odo->SetBorder("#ffffff",1);

$odo->scale->Set(0,10);
$odo->scale->SetTicks(1,1);
$odo->scale->SetTickColor('#ffffff');
$odo->scale->SetTickLength(0.05);
$odo->scale->SetTickWeight(10);
$odo->scale->SetLabelPos(0.8);
$odo->scale->SetLabelFormat('%d bar');

$odo->scale->label->SetFont(FF_DV_SANSSERIF,FS_NORMAL,16);
$odo->scale->label->SetColor('#ffffff');

$odo->needle->SetStyle(NEEDLE_STYLE_LARGE_TRIANGLE);
$odo->needle->Set($OdoData);
$odo->needle->SetShadow();

$odo->label->Set("bar");
$odo->label->SetFont(FF_DV_SANSSERIF,FS_BOLD,16);

$graph->Add($odo);


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
