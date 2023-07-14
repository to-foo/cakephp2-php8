<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_gantt');
/*
pr($data);
die();
*/
$Karenz = 12;
$SollColor = 'azure3';
$IstColor = 'peachpuff4';

$TitleFontColor = 'black';

$TitleFontColorFinish = 'forestgreen';
$TitleFontColorInTime = 'dodgerblue3';
$TitleFontColorError = 'red';
$TitleFontColorHint = 'orange3';

$graph = new GanttGraph();
$graph = new GanttGraph(2000,0);
$graph->SetMarginColor('gray:1.7');
$graph->SetColor('white');


// Setup the graph title and title font
$graph->title->Set(__("Overview delivering dates",true));
$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,14);

$graph->SetDateRange($data['statistic']['start_padding_date'],$data['statistic']['end_padding_date']);

// Show three headers
$graph->ShowHeaders(GANTT_HMONTH | GANTT_HYEAR);
// Set the column headers and font
$graph->scale->actinfo->SetColTitles( array(__('Suppliers',true),__('Start',true),__('End',true),__('Delay',true)),array(100));
$graph->scale->actinfo->SetFont(FF_DV_SANSSERIF,FS_BOLD,11);
$graph->scale->divider->SetWeight(3);

$x = 0;

if(!isset($SupplierIndexFilter)) {

	$ThisTitleFontColor = $TitleFontColor;

	if(isset($data['statistic']['progress'])){
		if($data['statistic']['progress'] == 1){
			$ThisTitleFontColor = $TitleFontColorFinish;
		} else {
			if($data['statistic']['days_arrears'] == 0){
				$ThisTitleFontColor = $TitleFontColorInTime;
			} else {
				$ThisTitleFontColor = $TitleFontColorHint;
				if($data['statistic']['days_arrears'] > $Karenz){
					$ThisTitleFontColor = $TitleFontColorError;
				}
			}
		}
	}

	$bar = new GanttBar($x ,array(
			'TRM '. $data['statistic']['percent'],
			'',
			''
		),
		$data['statistic']['start_diagramm_date'],
		$data['statistic']['end_diagramm_date'],
		'',
		0.35
	);

	// For each group make the name bold but keep the dates as the default font
	$bar->title->SetColumnFonts(array(array(FF_DV_SANSSERIF,FS_BOLD,11)));
	// Add group markers
	$bar->leftMark->SetType( MARK_LEFTTRIANGLE );
	$bar->leftMark->Show();
	$bar->rightMark->SetType( MARK_RIGHTTRIANGLE );
	$bar->rightMark->Show();
	$bar->progress->Set($data['statistic']['progress']);
	$bar->progress->SetFillColor($ThisTitleFontColor);
	$bar->progress->SetPattern(BAND_SOLID,$ThisTitleFontColor);
	$bar->SetFillColor('white');
	$bar->SetPattern(BAND_SOLID,'white');
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetColor($ThisTitleFontColor);
	$graph->Add($bar);


	$x++;

	$bar = new GanttBar($x,array(
			'  ' . __('planned delivery',true),
			$data['statistic']['start_soll_date'],
			$data['statistic']['end_soll_date'],' '
		),
		$data['statistic']['start_soll_date'],
		$data['statistic']['end_soll_date'],
		'',
		0.45);

	$bar->SetPattern(BAND_RDIAG,$SollColor);
	$bar->SetFillColor($SollColor);
	$bar->SetColor($SollColor);
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetColor($ThisTitleFontColor);
	$graph->Add($bar);

	$x++;

	$bar = new GanttBar($x,array(
			'  ' . __('delivery made',true),
			$data['statistic']['start_ist_date'],
			$data['statistic']['end_ist_date'],
			$data['statistic']['days_arrears'] . ' Tage'
		),
		$data['statistic']['start_ist_date'],
		$data['statistic']['end_ist_date'],
		'',
		0.45
	);

	$bar->SetPattern(BAND_RDIAG,$IstColor);
	$bar->SetFillColor($IstColor);
	$bar->SetColor($IstColor);
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetColor($ThisTitleFontColor);
	$graph->Add($bar);

	$x++;
	$x++;

}

foreach ($data['responsibility'] as $key => $value) {

	if(!isset($value['statistic'])) continue;

	if($value['statistic']['start_soll_date'] == 0) continue;
	if($value['statistic']['end_soll_date'] == 0) continue;

	if($value['statistic']['start_ist_date'] == 0) continue;
	if($value['statistic']['end_ist_date'] == 0) continue;

	$ThisTitleFontColor = $TitleFontColor;

	if(isset($value['statistic']['progress'])){
		if($value['statistic']['progress'] == 1){
			$ThisTitleFontColor = $TitleFontColorFinish;
		} else {
			if($value['statistic']['days_arrears'] == 0){
				$ThisTitleFontColor = $TitleFontColorInTime;
			} else {
				$ThisTitleFontColor = $TitleFontColorHint;
				if($value['statistic']['days_arrears'] > $Karenz){
					$ThisTitleFontColor = $TitleFontColorError;
				}
			}
		}
	}

	$bar = new GanttBar($x,array(
				$key . ' ' . $value['statistic']['percent'],
				'',
				''
			),
		$value['statistic']['start_diagramm_date'],
		$value['statistic']['end_diagramm_date'],
		'',
		0.35
	);

	// For each group make the name bold but keep the dates as the default font
	$bar->title->SetColumnFonts(array(array(FF_DV_SANSSERIF,FS_BOLD,11)));
	// Add group markers
	$bar->leftMark->SetType( MARK_LEFTTRIANGLE );
	$bar->leftMark->Show();
	$bar->rightMark->SetType( MARK_RIGHTTRIANGLE );
	$bar->rightMark->Show();
	$bar->progress->Set($value['statistic']['progress']);
	$bar->progress->SetFillColor($ThisTitleFontColor);
	$bar->progress->SetPattern(BAND_SOLID,$ThisTitleFontColor);
	$bar->SetFillColor('white');
	$bar->SetPattern(BAND_SOLID,'white');
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetColor($ThisTitleFontColor);


	$graph->Add($bar);

	$x++;

	$bar = new GanttBar($x,array(
			'  ' . __('planned delivery',true),
			$value['statistic']['start_soll_date'],
			$value['statistic']['end_soll_date'],
			' '
		),
		$value['statistic']['start_soll_date'],
		$value['statistic']['end_soll_date'],
		'',
		0.45
	);

	$bar->SetPattern(BAND_RDIAG,$SollColor);
	$bar->SetFillColor($SollColor);
	$bar->SetColor($SollColor);
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetColor($ThisTitleFontColor);

	$graph->Add($bar);

	$x++;

	$bar = new GanttBar($x,array(
			'  ' . __('delivery made',true),
			$value['statistic']['start_ist_date'],
			$value['statistic']['end_ist_date'],
			$value['statistic']['days_arrears'] . ' Tage'
		),
		$value['statistic']['start_ist_date'],
		$value['statistic']['end_ist_date'],
		'',
		0.45
	);

	$bar->SetPattern(BAND_RDIAG,$IstColor);
	$bar->SetFillColor($IstColor);
	$bar->SetColor($IstColor);
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetColor($ThisTitleFontColor);
	$graph->Add($bar);

	$x++;
	$x++;
	$x++;


	if(isset($value['suppliers']) && isset($SupplierIndexFilter)){

		foreach ($value['suppliers'] as $_key => $_value) {

			if(!isset($_value['statistic']['start_soll_date'])) continue;
			if(!isset($_value['statistic']['end_soll_date'])) continue;
			if(!isset($_value['statistic']['start_diagramm_date'])) continue;
			if(!isset($_value['statistic']['end_diagramm_date'])) continue;
			if(!isset($_value['statistic']['start_ist_date'])) continue;
			if(!isset($_value['statistic']['end_ist_date'])) continue;

			$bar = new GanttBar($x,array(
						$_key . ' (' . $_value['statistic']['percent'] . ')',
						'',
						''
					),
				$_value['statistic']['start_diagramm_date'],
				$_value['statistic']['end_diagramm_date'],
				'',
				0.35
			);

			$ThisTitleFontColor = $TitleFontColor;

			if(isset($_value['statistic']['progress'])){
				if($_value['statistic']['progress'] == 1){
					$ThisTitleFontColor = $TitleFontColorFinish;
				} else {
					if($_value['statistic']['days_arrears'] == 0){
						$ThisTitleFontColor = $TitleFontColorInTime;
					} else {
						$ThisTitleFontColor = $TitleFontColorHint;
						if($_value['statistic']['days_arrears'] > $Karenz){
							$ThisTitleFontColor = $TitleFontColorError;
						}
					}
				}
			}

			$bar->title->SetColumnFonts(array(array(FF_DV_SANSSERIF,FS_BOLD,11)));
			$bar->leftMark->SetType( MARK_LEFTTRIANGLE );
			$bar->leftMark->Show();
			$bar->rightMark->SetType( MARK_RIGHTTRIANGLE );
			$bar->rightMark->Show();
			$bar->progress->Set($_value['statistic']['progress']);
			$bar->progress->SetFillColor($ThisTitleFontColor);
			$bar->progress->SetPattern(BAND_SOLID,$ThisTitleFontColor);
			$bar->SetFillColor('white');
			$bar->SetPattern(BAND_SOLID,'white');
			$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
			$bar->title->SetColor($ThisTitleFontColor);
			$graph->Add($bar);

			$x++;

			$bar = new GanttBar($x,array(
					'  ' . __('planned delivery',true),
					$_value['statistic']['start_soll_date'],
					$_value['statistic']['end_soll_date'],
					' '
				),
				$_value['statistic']['start_soll_date'],
				$_value['statistic']['end_soll_date'],
				'',
				0.45
			);

			$bar->SetPattern(BAND_RDIAG,$SollColor);
			$bar->SetFillColor($SollColor);
			$bar->SetColor($SollColor);
			$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
			$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
			$bar->title->SetColor($ThisTitleFontColor);
			$graph->Add($bar);

			$x++;

			$bar = new GanttBar($x,array(
					'  ' . __('delivery made',true),
					$_value['statistic']['start_ist_date'],
					$_value['statistic']['end_ist_date'],
					$_value['statistic']['days_arrears'] . ' Tage'
				),
				$_value['statistic']['start_ist_date'],
				$_value['statistic']['end_ist_date'],
				'',
				0.45
			);

			$bar->SetPattern(BAND_RDIAG,$IstColor);
			$bar->SetFillColor($IstColor);
			$bar->SetColor($IstColor);
			$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
			$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
			$bar->title->SetColor($ThisTitleFontColor);
			$graph->Add($bar);

			$ThisTitleFontColor = $TitleFontColor;

			$x++;
			$x++;

		}
	}

}

//die();
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
