<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_date');

$EnDescription  = __('Der Verzug in Tagen beträgt',true) . " " . $AverageDelayAll['result'] . " (errechnet aus dem durchschnittlichen Verzug aller Bestellungen)";

$EnText = new Text($EnDescription);
$EnText->SetPos(75,50);
$EnText->SetFont(FF_DV_SANSSERIF,FS_NORMAL,12);
$EnText->ParagraphAlign('left');
$EnText->SetBox('#fffff','#fffff','gray',0,0);
$EnText->SetColor('#ff0000');

$EnText2 = new Text("TA-Periode\nKW 35 - KW 41");
$EnText2->SetPos(1028,42);
$EnText2->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
$EnText2->ParagraphAlign('left');
$EnText2->SetBox('#fffff','#fffff','gray',0,0);
$EnText2->SetColor('green');

//$dateUtils = new DateScaleUtils();
$datay1 = array_values($AllEquipments['plan']);
$datay2 = array_values($AllEquipments['ist']);
$datay3 = array_values($AllEquipments['targeted']);

// Setup the graph
$graph = new Graph(1250,750);
$graph->SetScale("textlin");
//$graph->xaxis->scale->SetTimeAlign( YEARADJ_2 );
$graph->xaxis->scale->SetGrace(50);
$theme_class=new UniversalTheme;

$graph->SetTheme($theme_class);
$graph->title->Set('Zeitlicher Verlauf geplante und erfolgte Liefertermine');
$graph->SetBox(false);

$graph->SetMargin(75,100,75,200);

$graph->img->SetAntiAliasing();

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);
$graph->yaxis->SetTitle('Anzahl Equipments','middle');
$graph->yaxis->SetTitlemargin(40);

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
$graph->xaxis->SetTickLabels(array('A','B','C','D'));
$graph->xgrid->SetColor('#E3E3E3');

$graph->xaxis->SetTickLabels(array_keys($AllEquipments['ist']));
$graph->xaxis->SetLabelAngle(90);
/*
$x = 0;
foreach($AllEquipments['plan'] as $key => $data){
	pr($key);
	pr($x);
	$x++;
}
die();
*/
// Create the first line
$p1 = new LinePlot($datay1);
$graph->Add($p1);
$p1->SetColor("#6495ED");
$p1->SetWeight(4);
$p1->SetLegend('Geplanter Liefertermin');
//$p1->SetFillColor('#6495ED@.6');

// Create the second line
$p2 = new LinePlot($datay2);
$graph->Add($p2);
$p2->SetWeight(5);
$p2->SetColor("#B22222");
$p2->SetLegend('Erfolgter Liefertermin');
//$p2->SetFillColor('#B22222@.6');
$p4 = new LinePlot($datay2);
$graph->Add($p4);
$p4->SetWeight(5);
$p4->SetColor("#B22222");
//$p2->SetFillColor('#B22222@.6');

// Create the third line
$p3 = new LinePlot($datay3);
$graph->Add($p3);
$p3->SetWeight('5');
$p3->SetColor("#d6c853");
$p3->SetLegend('Anvisierter Liefertermin');
//$p3->SetFillColor('#d6c853@.6');
$p3->AddArea(92,95,LP_AREA_FILLED,"green");

$graph->legend->SetFrameWeight(1);
//$graph->img->SetAntiAliasing(false);

if(isset($EnDescription)) $graph->AddText($EnText);
if(isset($EnDescription)) $graph->AddText($EnText2);


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
die();
?>
