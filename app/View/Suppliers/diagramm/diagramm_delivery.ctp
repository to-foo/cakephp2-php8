<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_bar');
App::import('Vendor', 'jpgraph/jpgraph_table');

//bar3
/*
$data[Zustand z.B. (überschritten)][Zugehörigkeit z.B. (2020+)] = array('Firma Wert','Firma Wert');
$data[Zustand z.B. (überschritten)][Zugehörigkeit z.B. (TA2020)] = array('Firma Wert','Firma Wert');

$data[Zustand z.B. (pünktlich)][Zugehörigkeit z.B. (2020+)] = array('Firma Wert','Firma Wert');
$data[Zustand z.B. (pünktlich)][Zugehörigkeit z.B. (TA2020)] = array('Firma Wert','Firma Wert');


$data[Zustand z.B. (kein Datum)][Zugehörigkeit z.B. (2020+)] = array('Firma Wert','Firma Wert');
$data[Zustand z.B. (kein Datum)][Zugehörigkeit z.B. (TA2020)] = array('Firma Wert','Firma Wert');
*/

$datay['intime'][0] = array(220,230,210,175,185,195,200,230,200,195,180,130);
$datay['intime'][1] = array(40,45,70,80,50,75,70,70,80,75,80,50);
$datay['intime'][2] = array(20,20,25,22,30,25,35,30,27,25,25,45);
$datay['outtime'][0] = array(120,130,210,175,185,125,200,230,200,195,180,130);
$datay['outtime'][1] = array(40,45,70,40,50,75,70,70,20,75,80,50);
$datay['outtime'][2] = array(20,20,25,22,10,25,35,30,57,25,35,45);
$datay['nodate'][0] = array(120,130,210,175,185,125,200,230,200,195,180,130);
$datay['nodate'][1] = array(40,45,70,40,50,75,70,70,20,75,80,50);
$datay['nodate'][2] = array(20,20,25,22,10,25,35,30,57,25,35,45);
//line1
//$data6y=array(50,58,60,58,53,58,57,60,58,58,57,50);

//foreach ($data6y as &$y) { $y -= 10; }

// Create the graph. These two calls are always required
$graph = new Graph(3000,1500,'auto');
$graph->SetScale("textlin");
$graph->SetY2Scale("lin",0,90);
$graph->SetY2OrderBack(false);

$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

$graph->SetMargin(40,20,46,80);

//$graph->yaxis->SetTickPositions(array(0,50,100,150,200,250,300,350), array(25,75,125,175,275,325));
//$graph->y2axis->SetTickPositions(array(30,40,50,60,70,80,90));

//$months = $gDateLocale->GetShortMonth();
//$months = array_merge(array_slice($months,3,9), array_slice($months,0,3));
$graph->SetBox(false);

$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels(array('Firma A','Firma B','Firma C','Firma D'));
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

$bplot = array();
$colors = array(0 => '#0000CD',1 => '#B0C4DE',2 => '#8B008B',3 => '#8B008C');

foreach ($DataForPlot as $key => $value) {
	foreach ($value as $_key => $_value) {
		$bplot[$key][$_key] = new BarPlot($_value);
	}
	$gbbplot[] = new AccBarPlot($bplot[$key]);
}

/*
$b3plot = new BarPlot($data3y);
$b4plot = new BarPlot($data4y);
$b5plot = new BarPlot($data5y);
*/
//$lplot = new LinePlot($data6y);

// Create the grouped bar plot
//$gbbplot = new AccBarPlot(array($b3plot,$b4plot,$b5plot));
$gbplot = new GroupBarPlot($gbbplot);

// ...and add it to the graPH
$graph->Add($gbplot);
//$graph->AddY2($lplot);

foreach ($DataForPlot as $key => $value) {
	/*
	foreach ($value as $_key => $_value) {
		$bplot[$key][$_key]->SetColor($colors[$_key]);
		$bplot[$key][$_key]->SetFillColor($colors[$_key]);

		if($_key == 0){
			$bplot[$key][$_key]->value->SetFormat('Test');
			$bplot[$key][$_key]->value->setFont(FF_FONT1,FS_NORMAL, 7);
			$bplot[$key][$_key]->value->setAngle(90);
			$bplot[$key][$_key]->value->HideZero();
			$bplot[$key][$_key]->value->SetAlign('left','center');
			$bplot[$key][$_key]->value->show();
		}
	}
	*/
}

/*
$lplot->SetBarCenter();
$lplot->SetColor("yellow");
$lplot->SetLegend("Houses");
$lplot->mark->SetType(MARK_X,'',1.0);
$lplot->mark->SetWeight(2);
$lplot->mark->SetWidth(8);
$lplot->mark->setColor("yellow");
$lplot->mark->setFillColor("yellow");
*/
$graph->legend->SetFrameWeight(1);
$graph->legend->SetColumns(6);
$graph->legend->SetColor('#4E4E4E','#00A78A');
/*
$band = new PlotBand(VERTICAL,BAND_RDIAG,11,"max",'khaki4');
$band->ShowFrame(true);
$band->SetOrder(DEPTH_BACK);
$graph->Add($band);
*/
$graph->yaxis->scale->SetGrace(20);
$graph->title->Set("Combined Line and Bar plots");

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
