<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_bar');
App::import('Vendor', 'jpgraph/jpgraph_table');

/************************************************************************************************
 *																								*
 * Daten für Anzeige erzeugen																		*
 *																								*
 ************************************************************************************************/
// Anzahl der Tage im Monat feststellen
// Template für Bars erzeugen, damit jeder vorkommende Tag des Monat überall zumindest mit 0 vertreten ist

if(!isset($Verfahren)) return false;

if(count($List) == 0){
	echo 'no data to display';
	return;
};

$TableData[0][0] = __(' ',true);
$TableData[1][0] = __('all',true);
$TableData[2][0] = __('e',true);
$TableData[3][0] = __('ne',true);
$TableData[4][0] = __('-',true);

foreach($List as $_key => $_data){
	$label = explode('-',$_key);
	$TableData[0][$_key] = $label[2] . ".\n" . $label[1] . '.';
	$TableData[1][$_key] = 0;
	$TableData[2][$_key] = 0;
	$TableData[3][$_key] = 0;
	$TableData[4][$_key] = 0;
}

$TableData[0][] = __('all',true);

foreach($List as $_key => $_data){
	$TableData[1][$_key] += $_data['all_count'];
	$TableData[2][$_key] += $_data['e_count'];
	$TableData[3][$_key] += $_data['ne_count'];
	$TableData[4][$_key] += $_data['non_count'];
}



$TableData[1][] = array_sum($TableData[1]);
$TableData[2][] = array_sum($TableData[2]);
$TableData[3][] = array_sum($TableData[3]);
$TableData[4][] = array_sum($TableData[4]);

if(array_sum($TableData[3]) > 0 && array_sum($TableData[1]) > 0){
	$EnValue = round(100 * array_sum($TableData[3]) / array_sum($TableData[1]),2);
} else {
	$EnValue = '0';
}

$EnDescription  = __('Die Reparaturquote',true) . " ";
if(isset($SearchingDescription['Reportnumber']['created'])) $EnDescription .= $SearchingDescription['Reportnumber']['created'] . " ";
$EnDescription .= __('beträgt',true) . ' ' . $EnValue . ' ' . __('Percent',true);
$EnText = new Text($EnDescription);
$EnText->SetPos(10,225);
$EnText->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
$EnText->ParagraphAlign('left');
$EnText->SetBox('#fffff','#fffff','gray',0,0);
$EnText->SetColor('#ff0000');

$max = 0;
//$tickLabels = $Months;
$datas = array('total' => array(),'e' => array(),'ne' => array(),'n' => array());
$dots = array();

foreach($List as $_key => $_days){
		$tickLabels[] = $_key;
		$datas['total'][] = $_days['all_count'];
		$datas['e'][] = $_days['e_count'];
		$datas['ne'][] = $_days['ne_count'];
		$datas['n'][] = $_days['non_count'];
		if($_days['all_count'] > 0 && ($_days['ne_count'] + $_days['e_count'] > 0)) $dots[] = 100 * $_days['ne_count'] / ($_days['ne_count'] + $_days['e_count']);
		else $dots[] = 0;
}

if(count($datas['total']) > 0){
	$max = max($datas['total']);
} else {
	return;
}

/************************************************************************************************
 * 																								*
 * Suchbeschreibung erzeugen																			*
 * 																								*
 ************************************************************************************************/

if(isset($SearchingDescription) && is_array($SearchingDescription)){
	$Description = "";
	foreach($SearchingDescription as $_key => $_data){
		foreach($_data as $__key => $__data){
			$Description .= $__data . "\n";
		}
	}

	$txt = new Text($Description);
	$txt->SetPos(10,1,'left');
	$txt->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$txt->ParagraphAlign('left');
	$txt->SetBox('#fffff','#fffff','gray',0,0);
	$txt->SetColor('#000000');
}

/************************************************************************************************
 * 																								*
 * Datentabelle erzeugen																			*
 * 																								*
 ************************************************************************************************/

$nbrbar = 6;
$cellwidth = 35;
$tableypos = 1;
$tablexpos = 400;
$tablewidth = $nbrbar*$cellwidth;
$TableNew = array();

foreach($TableData as $_key => $_data){
	$TableNew[$_key] = array();
	foreach($_data as $__key => $__data) $TableNew[$_key][] = $__data;
}

$table = new GTextTable();
$table->Set($TableNew);
$table->SetPos($tablexpos,$tableypos+1);

// Basic table formatting
$table->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
$table->SetAlign('right');
$table->SetMinColWidth($cellwidth);

$table->SetRowFillColor(0,'#aacceb@0.7');
$table->SetRowFont(0,FF_DV_SANSSERIF,FS_BOLD,8);
$table->SetRowAlign(0,'center');
$table->SetBorder(1,'gray');

/************************************************************************************************
 * 																								*
 * Den Graph erzeugen																			*
 * 																								*
 ************************************************************************************************/

$theme = new UniversalTheme();

$barcolors = array(
			'total'=>'#9dadde',	#blue
            'e'=>'#81bf7f',		#green
            'ne'=>'#ff7f7f',	#red
            'n'=>'#b1b1b1');

// Create the graph.
$graph = new Graph(max($ImageWidth * .95, 1300), max($ImageHeight, 700));
$graph->SetScale('textlin', 0, $max);
$graph->ClearTheme();
$graph->img->SetMargin(50,20,270,40);

$graph->xaxis->SetTickLabels(array_values($tickLabels));
$graph->xaxis->SetLabelAngle(45);// Graphen aus Bardaten erzeugen
$plot = array();
foreach($datas as $type => $data) {
	$plot[$type] = new BarPlot(($data));
	$theme->ApplyPlot($plot[$type]);
	$plot[$type]->SetLegend(__('Weld type '.$type, true));
	$plot[$type]->SetFillColor($barcolors[$type]);
	$plot[$type]->SetColor('lightgray');
	$plot[$type]->value->SetFormat('%u');
	$plot[$type]->value->setFont(FF_FONT1,FS_NORMAL, 7);
	$plot[$type]->value->setAngle(90);
	$plot[$type]->value->HideZero();
	$plot[$type]->value->show();
}

$graph->Add(new GroupBarPlot(array_values($plot)));

$yrange = range(0, $max);
$yrangeVal = array_unique(array_merge(array_map('round', range(0, 100, 100/$max), array_fill(0, $max, 0)), array(100)));

$_steps = array_merge(array(0), array_map('end', array_chunk($yrange, max(1,count($yrange)/10))));
$_stepVal = array_merge(array(0), array_map('end', array_chunk($yrangeVal, max(1,count($yrangeVal)/10))));

$lineplot=new LinePlot(array_values($dots));
$lineplot->SetLegend(__('Weld type ne', true).' in '.__('Percent', true));
$lineplot->SetColor("red");
$lineplot->SetBarCenter();
$lineplot->value->SetFormat('%.1f%%');
$lineplot->value->HideZero();
$lineplot->value->SetAlign('right');
$lineplot->value->SetColor('red');
$lineplot->value->show(true);
$lineplot->mark->SetType(MARK_FILLEDCIRCLE, 'red', 2);
$lineplot->mark->SetColor('red');
$lineplot->mark->SetFillColor('red');
$lineplot->mark->Show();

// Add the plot to the graph
$graph->SetY2OrderBack(false);
$graph->SetY2Scale('int', 0, 100);
$graph->AddY2($lineplot);

// Setup graph layout
$theme->SetupGraph($graph);

$graph->yaxis->scale->SetGrace(5);
//$graph->yaxis->SetTitle(__('Percent', true), 'middle');

$graph->y2axis->scale->SetGrace(5);
$graph->y2axis->Hide();

$graph->legend->SetPos(0.02, 0.01, 'right', 'top');
$graph->legend->SetLayout(LEGEND_VERT);
$graph->legend->SetFillColor('#ffff');
$graph->legend->SetShadow(0);
$graph->legend->SetFrameWeight(2);

//$graph->title->Set(__(date('F', $month_ts)).' '.date('Y', $month_ts));
//$graph->title->Set(__('Overview',true) . ' ' . __('from',true) . ' ' . $PeriodoFTime[0] . ' ' . __('to',true) . ' ' . $PeriodoFTime[1] . ' (' . __('creation date of the testing reports',true) . ')');
$graph->title->SetAlign('left');

if(count($TableData) > 0) $graph->Add($table);
if(isset($SearchingDescription)) $graph->AddText($txt);
if(isset($EnDescription)) $graph->AddText($EnText);

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
?>
