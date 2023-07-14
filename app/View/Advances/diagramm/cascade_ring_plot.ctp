<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_pie');

if(!isset($data['result'])) return null;

if(isset($data['Color']['advance_line_color'])) $StatusColor = $data['Color']['advance_line_color'];
elseif(isset($data['advance_line_color'])) $StatusColor = $data['advance_line_color'];
else $StatusColor = '#3c5bbe';

$datapie = array($data['result']['open'],$data['result']['okay'],$data['result']['error']);

// A new pie graph
$graph = new PieGraph(300,300,'auto');

// Setup title
$graph->title->Set(__($key,true));
$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,12);
$graph->title->SetMargin(8); // Add a little bit more margin from the top

$graph->legend->SetFillColor('#ffff');
$graph->legend->SetShadow(0);
$graph->legend->SetFrameWeight(1);
$graph->legend->SetPos(0.5,0.97,'center','bottom');
$graph->legend->SetColumns(1);
// Create the pie plot
$p1 = new PiePlotC($datapie);

// Set size of pie
$p1->SetSize(0.32);
$p1->SetLegends(array(__('Advance',true) . ' ',__('Repairs',true) . ' '));

$p1->SetSliceColors(array('#aebbc8',$StatusColor,'#ff0000'));
$p1->SetStartAngle(90);
// Label font and color setup
$p1->value->SetFont(FF_DV_SANSSERIF,FS_BOLD,10);
$p1->value->SetColor('white');

// Setup the title on the center circle
//$p1->midtitle->Set("Test mid\nRow 1\nRow 2");
$p1->midtitle->SetFont(FF_DV_SANSSERIF,FS_BOLD,16);

$Percent = (round(100 * $data['result']['advance_all_deci'],2));

// Setup the title on the center circle
$p1->midtitle->Set($Percent . "%");
// Set color for mid circle
$p1->SetMidColor('#ffff');
// Use percentage values in the legends values (This is also the default)
//$p1->SetLabelType(PIE_VALUE_PER);

// Add plot to pie graph
$graph->Add($p1);

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
