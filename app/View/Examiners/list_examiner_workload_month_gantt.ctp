<?php
App::uses('File', 'Utility');

$css = new File(APP.'webroot'.DS.'css'.DS.'cake.default.css');
if(!preg_match('/Wartezeit[:\s]+(#[a-f0-9]+)/i', $css->read(), $waiting_color))
	$waiting_color = array(null, '#FF9D52');
$waiting_color[1] = $waiting_color[1].'@0.5';

if(!preg_match('/Prüfzeit[:\s]+(#[a-f0-9]+)/i', $css->read(), $testing_color))
	$testing_color = array(null, '#529DFF');
$testing_color[1] = $testing_color[1].'@0.5';

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

$days = array();		// Pro Datum im Gantt-Diagramm nur eine Zeile
if(!empty($waiting)) {
	$waiting_plots = array();
	$span = array();
	foreach($waiting as $id=>$time) {
		if(array_search(date('d.m.Y',$time), $days) === false) $days[] = date('d.m.Y',$time);
		
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
		if(array_search(date('d.m.Y',$time), $days) === false) $days[] = date('d.m.Y',$time);

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

sort($days);			// Zeilen nach Datum sortieren

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
 
$Graph->ShowHeaders(GANTT_HHOUR);
//$Graph->scale->SetRange($limits[0], $limits[1]);
$Graph->scale->hour->SetIntervall(1);
$Graph->scale->hour->SetStyle(HOURSTYLE_HM24);
$Graph->scale->hour->SetBackgroundColor('lightyellow:1.5');
$Graph->scale->hour->SetFont(FF_FONT1);
$Graph->scale->day->SetFont(DAYSTYLE_LONG);

// Format the bar for the first activity
$Graph->scale->actinfo->SetColTitles(array('Datum'),array(50));
$Graph->scale->actinfo->SetBackgroundColor('black@0.8');

// 0 % vertical label margin
$Graph->SetLabelVMarginFactor(1); // 1=default value

$activity = $noactivity = array();

$statistics = array('wait'=>array(), 'test'=>array());
if(!empty($testing_plots)) {
	array_walk($testing_plots, function($elem, $id) use($testing_plots, $Graph, $limits, $days, &$statistics, $testing_color) {
		if(!isset($statistics['test'][date('d.m.Y', $elem[0])])) $statistics['test'][date('d.m.Y', $elem[0])] = array('min'=>min($elem[0], $elem[1]), 'max'=>max($elem[0], $elem[1]), 'len'=>0);
		$statistics['test'][date('d.m.Y', $elem[0])]['min'] = min($statistics['test'][date('d.m.Y', $elem[0])]['min'], $elem[0], $elem[1]);
		$statistics['test'][date('d.m.Y', $elem[0])]['max'] = max($statistics['test'][date('d.m.Y', $elem[0])]['max'], $elem[0], $elem[1]);
		$statistics['test'][date('d.m.Y', $elem[0])]['len'] += abs($elem[0] - $elem[1]);
	
		$plot = new GanttBar(array_search(date('d.m.Y', $elem[0]), $days), date('d.m.Y', $elem[0]), $limits[0].' '.date('H:i', $elem[0]), $limits[0].' '.date('H:i', $elem[1]));
		$plot->SetPattern(GANTT_SOLID, "white@1");
		$plot->SetFillColor($testing_color[1]);
		$plot->SetHeight(16);
	
		$Graph->Add($plot);
	});
}

if(!empty($waiting_plots)) {
	array_walk($waiting_plots, function($elem, $id) use($waiting_plots, $Graph, $limits, $days, &$statistics, $waiting_color) {
		if(!isset($statistics['wait'][date('d.m.Y', $elem[0])])) $statistics['wait'][date('d.m.Y', $elem[0])] = array('min'=>min($elem[0], $elem[1]), 'max'=>max($elem[0], $elem[1]), 'len'=>0);
		$statistics['wait'][date('d.m.Y', $elem[0])]['min'] = min($statistics['wait'][date('d.m.Y', $elem[0])]['min'], $elem[0], $elem[1]);
		$statistics['wait'][date('d.m.Y', $elem[0])]['max'] = max($statistics['wait'][date('d.m.Y', $elem[0])]['max'], $elem[0], $elem[1]);
		$statistics['wait'][date('d.m.Y', $elem[0])]['len'] += abs($elem[0] - $elem[1]);
	
		$plot = new GanttBar(array_search(date('d.m.Y', $elem[0]), $days), date('d.m.Y', $elem[0]), $limits[0].' '.date('H:i', $elem[0]), $limits[0].' '.date('H:i', $elem[1]));
		$plot->SetPattern(GANTT_SOLID, "white@1");
		$plot->SetFillColor($waiting_color[1]);
		$plot->SetHeight(16);
	
		$Graph->Add($plot);
	});
}

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
?>
<h3><?php echo __(date('F', strtotime($limits[0]))); ?></h3>

<table>
	<tr>
    	<th>Datum</th>
    	<th>Wartezeiten</th>
    	<th>gesamt</th>
    	<th>Prüfzeiten</th>
    	<th>gesamt</th>
    	<th>Total</th>
    </tr>
<?php
	$ln = 1;
	$d = array_keys(array_merge($statistics['wait'], $statistics['test']));
	$testtime = 0;
	$waittime = 0;
	sort($d);
	foreach($d as $date) {
		echo '<tr'.($ln++ % 2 ? ' class="altrow"' : '').'>'.PHP_EOL;
		echo '<td>'.$date.'</td>'.PHP_EOL;
		echo '<td>';
			if(isset($statistics['wait'][$date])) { echo date('H:i', $statistics['wait'][$date]['min']).' - '.date('H:i', $statistics['wait'][$date]['max']); }
		echo '</td>';
		echo '<td>';
			if(isset($statistics['wait'][$date])) {
				$waittime += $statistics['wait'][$date]['len'];
				$h = intval($statistics['wait'][$date]['len'] / 3600);
				$m = intval(($statistics['wait'][$date]['len'] - $h * 3600) / 60);
				echo sprintf('%02u:%02u', $h, $m);
			}
		echo '</td>';
		
		echo '<td>';
			if(isset($statistics['test'][$date])) { echo date('H:i', $statistics['test'][$date]['min']).' - '.date('H:i', $statistics['test'][$date]['max']); }
		echo '</td>';
		echo '<td>';
			if(isset($statistics['test'][$date])) {
				$testtime += $statistics['test'][$date]['len'];
				$h = intval($statistics['test'][$date]['len'] / 3600);
				$m = intval(($statistics['test'][$date]['len'] - $h * 3600) / 60);
				echo sprintf('%02u:%02u', $h, $m);
			}
		echo '</td>';
		
		echo '<td>';
			$tmp =	(isset($statistics['wait'][$date]['len']) ? intval($statistics['wait'][$date]['len']) : 0)
					+ (isset($statistics['test'][$date]['len']) ? intval($statistics['test'][$date]['len']) : 0);
			
			$h = intval($tmp / 3600);
			$m = intval(($tmp - $h * 3600) / 60);
			echo sprintf('%02u:%02u', $h, $m);
		echo '</td>';
		echo '</tr>';
	}
?>
	<tr<?php echo ($ln++ % 2 ? ' class="altrow"' : ''); ?>>
    	<td>Wartezeit gesamt</td>
    	<td colspan="5"><?php
			$h = intval($waittime / 3600);
			$m = intval(($waittime - $h * 3600) / 60);
			echo sprintf('%02u:%02u', $h, $m);
    	?></td>
    </tr>
	<tr<?php echo ($ln++ % 2 ? ' class="altrow"' : ''); ?>>
    	<td>Prüfzeit gesamt</td>
    	<td colspan="5"><?php
			$h = intval($testtime / 3600);
			$m = intval(($testtime - $h * 3600) / 60);
			echo sprintf('%02u:%02u', $h, $m);
    	?></td>
    </tr>
	<tr<?php echo ($ln++ % 2 ? ' class="altrow"' : ''); ?>>
    	<td>Total</td>
    	<td colspan="5"><?php
			$h = intval(($testtime+$waittime) / 3600);
			$m = intval((($testtime+$waittime) - $h * 3600) / 60);
			echo sprintf('%02u:%02u', $h, $m);
    	?></td>
    </tr>
</table>

<div class="paging">
<?php
	$day = $this->request->data['day']; unset($this->request->data['day']);
	$options = array_merge(array('class'=>'mymodal', 'data-formtarget'=>'#dialog .ui-tabs-panel[aria-hidden=\'false\']'), array('data-day[0]' => $day[0], 'data-day[1]' => $day[1]), array_combine(array_map(function($elem){return 'data-'.$elem;}, array_keys($this->request->data)), array_values($this->request->data)));
	ksort($options);
	if($this->Paginator->hasPrev()) echo $this->Paginator->prev(__('previous'), array_merge($options, array('class'=>'mymodal prev')));
	echo $this->Paginator->numbers(array_merge($options, array('separator'=>null)));
	if($this->Paginator->hasNext())echo $this->Paginator->next(__('next'), array_merge($options, array('class'=>'mymodal next')));
?>
<div class="clear"></div>
</div>

<?php
echo '<img src="data:image/png;base64,'.$g.'" />';

echo $this->JqueryScripte->ModalFunctions();
?>