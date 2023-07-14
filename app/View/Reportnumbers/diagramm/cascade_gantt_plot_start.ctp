<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_gantt');

$Karenz = $data['Advance']['karenz'];
$SollColor = 'azure3';
$IstColor = 'peachpuff4';

$TitleFontColor = 'black';

$ThisTitleFontColor = $TitleFontColor;

$TitleFontColorFinish = 'forestgreen';
$TitleFontColorInTime = 'dodgerblue3';
$TitleFontColorError = 'red';
$TitleFontColorHint = 'orange3';

$graph = new GanttGraph(2000,0);
$graph->SetMarginColor('white:1.7');
$graph->SetColor('white');
$graph->SetBox(false);
$graph->SetFrame(false,'black:0.25',1);
// Setup the graph title and title font
$graph->title->Set($data['CascadeGroupTitle']);
$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,12);
$graph->SetDateRange($data['Advance']['start'],$data['Advance']['end']);

// Show three headers
$graph->ShowHeaders( GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
$graph->scale->week->SetFont(FF_FONT0);
$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR2);

// Set the column headers and font
$graph->scale->actinfo->SetColTitles( array(__('Areas',true),__('Start',true),__('End',true),__('Interval',true),__('Rest time',true),__('Advance',true)),array(100));
$graph->scale->actinfo->SetFont(FF_DV_SANSSERIF,FS_BOLD,11);
$graph->scale->divider->SetWeight(3);

$y = 0;

foreach ($data['Scheme']['children'] as $key => $value) {

  if(!isset($value['schedule'])) continue;
  if(!isset($value['schedule']['start'])) continue;
  if(!isset($value['schedule']['end'])) continue;

  if(!isset($value['status'])) continue;

  $bar = new GanttBar($y,array(
				$value['discription'],
				$value['schedule']['start'],
				$value['schedule']['end'],
        $value['schedule']['interval'],
        $value['schedule']['resttime'],
        strval($value['status']['all_advance_methods']['advance_all_percent'])
			),
		$value['schedule']['start'],
		$value['schedule']['end'],
		0.35
	);

  $fmt = numfmt_create('en_EN', NumberFormatter::DECIMAL);
  $progress = numfmt_format($fmt, $value['status']['all_advance_methods']['advance_all_deci']);
  $bar->progress->Set($progress);
  $bar->progress->SetFillColor($value['schedule']['status_color']);
  $bar->progress->SetPattern(BAND_SOLID,$value['schedule']['status_color']);

  $bar->title->SetColumnFonts(array(array(FF_DV_SANSSERIF,FS_BOLD,11)));
  $bar->leftMark->SetType( MARK_LEFTTRIANGLE );
	$bar->leftMark->Show();
	$bar->rightMark->SetType(MARK_RIGHTTRIANGLE);
	$bar->rightMark->Show();
  $bar->SetFillColor('white');
  $bar->SetPattern(BAND_SOLID,'white');
  $bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
  $bar->title->SetColor($ThisTitleFontColor);

  $graph->Add($bar);

  $y++;

}

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
