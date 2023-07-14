<?php
App::uses('File', 'Utility');

$css = new File(APP.'webroot'.DS.'css'.DS.'cake.default.css');
if(!preg_match('/Wartezeit[:\s]+(#[a-f0-9]+)/i', $css->read(), $waiting_color))
	$waiting_color = array(null, '#FF9D52');
$waiting_color[1] = $waiting_color[1].'@0.5';

if(!preg_match('/Prüfzeit[:\s]+(#[a-f0-9]+)/i', $css->read(), $testing_color))
	$testing_color = array(null, '#529DFF');
$testing_color[1] = $testing_color[1].'@0.5';

$year = reset($examinerTimes);
$year = intval(date('Y', strtotime($year['ExaminerTime']['testing_time_start'])));

$waiting = $testing = array();
foreach($examinerTimes as $time) {
	if(!empty($time['ExaminerTime']['waiting_time_start']) && !empty($time['ExaminerTime']['waiting_time_end'])) {
		$daterange = new DatePeriod(
			$start = new DateTime($time['ExaminerTime']['waiting_time_start']),
			new DateInterval('PT1M'),
			$end = new DateTime($time['ExaminerTime']['waiting_time_end'])
		);
		
		foreach($daterange as $date):
			$waiting[] = $date->getTimestamp(); //$date->format('H') * 3600+$date->format('i')*60;
		endforeach;
		$waiting[] = $end->getTimestamp(); //format('H') * 3600+$end->format('i')*60;
	}

	if(!empty($time['ExaminerTime']['testing_time_start']) && !empty($time['ExaminerTime']['testing_time_end'])) {
		$daterange = new DatePeriod(
			$start = new DateTime($time['ExaminerTime']['testing_time_start']),
			new DateInterval('PT1M'),
			$end = new DateTime($time['ExaminerTime']['testing_time_end'])
		);
		
		foreach($daterange as $date):
			$testing[] = $date->getTimestamp(); //$date->format('H') * 3600+$date->format('i')*60;
		endforeach;
		$testing[] = $end->getTimestamp();
	}
}

$waiting = array_filter(array_unique($waiting));
$testing = array_filter(array_unique($testing));
sort($waiting);
sort($testing);

if(!empty($waiting)) {
	$waiting_plots = array();
	$span = array();
	foreach($waiting as $id=>$time) {
		if(count($span) < 2) {
			$span[] = $time;
		} else {
			if($span[1] < $time - 300) {	// Neuer Zeitabschnitt (mehr als 5 Minuten später)
				$waiting_plots[] = $span;
				$span = array($time);
			} elseif(intval(date('Hi', $time)) == 0) { // bei Überschreitung von Mitternacht nocht 23:59:59 hinzufügen und teilen
				$span[1] = $time-1;
				$waiting_plots[] = $span;
				$span = array($time);
			} else {
				$span[1] = $time;
			}
		}
	}
	if(!empty($span)) {
		$waiting_plots[] = $span;
	}
}

if(!empty($testing)) {
	$testing_plots = array();
	$span = array();
	foreach($testing as $id=>$time) {
		if(count($span) < 2) {
			$span[] = $time;
		} else {
			if($span[1] < $time - 300) {	// Neuer Zeitabschnitt (mehr als 5 Minuten später)
				$testing_plots[] = $span;
				$span = array($time);
			} elseif(intval(date('Hi', $time)) == 0) { // bei Überschreitung von Mitternacht nocht 23:59:59 hinzufügen und teilen
				$span[1] = $time-1;
				$testing_plots[] = $span;
				$span = array($time);
			} else {
				$span[1] = $time;
			}
		}
	}
	if(!empty($span)) {
		$testing_plots[] = $span;
	}
}

App::import('Vendor', 'jpgraph/jpgraph');
App::Import('Vendor', 'jpgraph/jpgraph_gantt');

$Graph = new GanttGraph(max($width, 1000));

// Setup some "very" nonstandard colors
$Graph->SetMarginColor('lightgreen@0.8');
$Graph->SetBox(true,'black',1);
$Graph->SetFrame(false,'darkgreen',4);

$Graph->title->Set("Example of hours in scale");
$Graph->title->SetColor('white');
$Graph->title->SetFont(FF_FONT1,FS_BOLD,14);
 
$Graph->ShowHeaders(null);
//$Graph->scale->SetRange($limits[0], $limits[1]);
$Graph->scale->hour->SetIntervall(12);
$Graph->scale->hour->SetStyle(HOURSTYLE_H24);
$Graph->scale->hour->SetBackgroundColor('lightyellow:1.5');
$Graph->scale->hour->SetFont(FF_FONT1);
$Graph->scale->day->SetFont(DAYSTYLE_LONG);

// Format the bar for the first activity
$Graph->scale->actinfo->SetColTitles(array(''),array(50));
$Graph->scale->actinfo->SetBackgroundColor('black@0.8');

//$rgb = new RGB($Graph->img);
//pr($rgb->allocate('#f9cfb9@0.2:0.9'));

// 0 % vertical label margin
$Graph->SetLabelVMarginFactor(0.01); // 1=default value

$months = array_map(function($month) {return __(date('F', mktime(0,0,0,$month,1,2000)));}, range(1,12));

$ln = 0;
?>
<div class="modalarea examiners view">
<h2>
<?php
echo __('Workload') . ' ';
if(isset($examiner['Examiner']['name'])) echo h($examiner['Examiner']['name']) . ' ' . __('for') . ' ';
echo __('Order') . ' ';
echo h($order['Order']['auftrags_nr']) . ' ';
echo __(date('Y', strtotime($this->request->data['year'].'-'.$this->request->data['month'].'-01'))); 
?>
</h2>
</h2>

<table>
	<tr>
		<th><?php echo __('Month'); ?></th>
		<th><?php echo __('Days with waiting times');?></th>
		<th><?php echo __('Waiting time'); ?></th>
		<th><?php echo __('Days with testing times'); ?></th>
		<th><?php echo __('Testing time'); ?></th>
		<th><?php echo __('Total'); ?></th>
	</tr>
	<?php
	foreach($months as $num=>$month) {
		$waiting = empty($waiting_plots) ? array() : array_filter($waiting_plots, function($elem) use($num) { return date('n', $elem[0]) == intval($num)+1; });
		$testing = empty($testing_plots) ? array() : array_filter($testing_plots, function($elem) use($num) { return date('n', $elem[0]) == intval($num)+1; });
		$hours_total = array_fill(0,12,0);

		echo '<tr'.($ln++ % 2 ? ' class="altrow"' : '');
		if(!empty($testing)) {
			echo ' style="cursor: pointer" onclick="
				$(\'.modalarea #datepicker\').datepicker( \'setDate\' ,\''.($num+1-date('n')).'m\');
				$(\'.modalarea #tabs\').tabs(\'option\', \'active\', 2);
			"';
		}
		echo '>'.PHP_EOL;
		echo '<td>'.$month.'</td>';
		echo '<td>';
			if(!empty($waiting)) {
				$days = array_unique(array_map(function($elem) { return date('d.m.Y', $elem[0]); }, $waiting));
				echo count($days);
			}
		echo '</td>';
		echo '<td>';
			if(!empty($waiting)) {
				$whours = $hours = array_reduce($waiting, function($prev, $curr) { return $prev + abs($curr[1]-$curr[0]); }, 0);
				$wh = $h = intval($hours / 3600);
				$wm = $m = intval(($hours - $h * 3600) / 60);
				echo sprintf('%02u:%02u', $h, $m);
				
				$hours_total[intval($num)] += $hours;
			} else {
				$whours = $wh = $wm = null;
			}
		echo '</td>';
		echo '<td>';
			if(!empty($testing)) {
				$days = array_unique(array_map(function($elem) { return date('d.m.Y', $elem[0]); }, $testing));
				echo count($days);
			}
		echo '</td>';
		echo '<td>';
			if(!empty($testing)) {
				$thours = $hours = array_reduce($testing, function($prev, $curr) { return $prev + abs($curr[1]-$curr[0]); }, 0);
				$th = $h = intval($hours / 3600);
				$tm = $m = intval(($hours - $h * 3600) / 60);
				echo sprintf('%02u:%02u', $h, $m);
				
				$hours_total[intval($num)] += $hours;
			} else {
				$thours = $th = $tm = null;
			}
		echo '</td>';
		echo '<td>';
			if($hours_total[intval($num)] > 0) {
				$h = intval($hours_total[intval($num)] / 3600);
				$m = intval(($hours_total[intval($num)] - $h * 3600) / 60);
				echo sprintf('%02u:%02u', $h, $m);
			}
		echo '</td>';
		echo '</tr>';

//		if(!empty($waiting)) {
//			$plot = new GanttBar(2*$num, $month, $limits[0].' 00:00', $limits[0].' '.sprintf('%02u:%02u', $wh, $wm));
//		}

		$hours = floatval(cal_days_in_month(CAL_GREGORIAN, $num+1, $year))*86400.0;

//		$plot = new GanttBar(2*$num, $month, $year.'-'.sprintf('%02u', $num+1).'-01 00:00', $year.'-'.sprintf('%02u', $num+1).'-'.cal_days_in_month(CAL_GREGORIAN, $num+1, $year).' 23:59');
		$cap = null;
		if(!empty($waiting)) { $cap = sprintf('%u:%u', $wh, $wm); }
		$plot = new GanttBar(2*$num, $month, $year.'-01-01 00:00', $year.'-01-31 23:59');
		$plot->progress->Set(floatval($whours)/$hours);
		$plot->progress->SetFillColor($waiting_color[1]);
		$plot->progress->SetPattern(BAND_SOLID, $waiting_color[1]);
		$plot->SetColor('white@1');
		$plot->SetPattern(GANTT_SOLID, 'white@1');
		$plot->SetFillColor('white@1');
		$plot->SetHeight(16);
		$Graph->Add($plot);

//		if(!empty($testing)) {	
//			$plot = new GanttBar(2*$num+1, $month, $limits[0].' 00:00', $limits[0].' '.sprintf('%02u:%02u', $th, $tm));
//		} else {
//		}

//		$plot = new GanttBar(2*$num+1, $month, $year.'-'.sprintf('%02u', $num+1).'-01 00:00', $year.'-'.sprintf('%02u', $num+1).'-'.cal_days_in_month(CAL_GREGORIAN, $num+1, $year).' 23:59');^
		$cap = null;
		if(!empty($testing)) { $cap = sprintf('%u:%u', $th, $tm); }
		$plot = new GanttBar(2*$num+1, '', $year.'-01-01 00:00', $year.'-01-31 23:59');
		$plot->progress->Set(floatval($thours)/$hours);
		$plot->progress->SetFillColor($testing_color[1]);
		$plot->progress->SetPattern(BAND_SOLID, $testing_color[1]);
		$plot->SetColor('white@1');
		$plot->SetPattern(GANTT_SOLID, 'white@1');
		$plot->SetFillColor('white@1');
		$plot->SetHeight(16);
		$Graph->Add($plot);
		
	}
	?>
</table>

<?php

$g = $Graph->Stroke(_IMG_HANDLER);
$size = array(imagesx($g), imagesy($g));

$black = imagecolorallocate($g, 0, 0, 0);
$white = imagecolorallocate($g, 255, 255, 255);

//$wait_color = RGB::Color('#f9cfb9@0.2:0.9');
$wait_color = RGB::Color($waiting_color[1]);
$wait_color = imagecolorallocatealpha($g, $wait_color[0], $wait_color[1], $wait_color[2], (1-$wait_color[3])*127);

//$test_color = RGB::Color('#f9d3e9@0.2:0.9');
$test_color = RGB::Color($testing_color[1]);
$test_color = imagecolorallocatealpha($g, $test_color[0], $test_color[1], $test_color[2], (1-$test_color[3])*127);

$left = $size[0]-230;
$top = 10;

imagefilledrectangle($g, $left, $top, $size[0]-18, 30, $white);
imagerectangle($g, $left, $top, $size[0]-18, 30, $black);

imagefilledrectangle($g, $left+7, $top+6, $left+16, $top+15, $wait_color);
//imagerectangle($g, $left+8, $top+6, $left+16, $top+14, $black);
imagestring($g, 2, $left+20, $top+4, (mb_detect_encoding(__('Waiting times'), 'UTF-8', true) ? utf8_decode(__('Waiting times')) : __('Waiting times')), $black);

imagefilledrectangle($g, $left+107, $top+6, $left+116, $top+15, $test_color);
//imagerectangle($g, $left+108, $top+6, $left+116, $top+14, $black);
imagestring($g, 2, $left+120, $top+4, (mb_detect_encoding(__('Testing times'), 'UTF-8', true) ? utf8_decode(__('Testing times')) : __('Testing times')), $black);

ob_start();
imagepng($g);
$g = base64_encode(ob_get_contents());
ob_end_clean();

echo '<img src="data:image/png;base64,'.$g.'" />';
?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions();?>