<?php
	App::import('Vendor', 'jpgraph/jpgraph');
	App::Import('Vendor', 'jpgraph/jpgraph_stock');

	$graphs = array();
	
	$tests = array();
	$month = null;
	$year = null;

	$Graph = new Graph(max($width, 1000), 600);
	$Graph->img->setMargin(50,10,10,30);
	$Graph->SetScale('textlin', 0, 24);
	
	$Graph->yaxis->SetLabelFormatCallback(function($tick) {
		return substr('0'.intval($tick), -2, 2).':00';
	});

	// Überschneidungen beim Datum zusammenziehen
	foreach ($examiners as $time):

		if(empty($time['ExaminerTime']['start']) || empty($time['ExaminerTime']['end'])) continue;
		
		$begin = new DateTime($time['ExaminerTime']['start']);
		$end = new Datetime($time['ExaminerTime']['end']);
		
		$month = $begin->format('m');
		$year = $begin->format('Y');
		
		$daterange = new DatePeriod($begin, new DateInterval('PT5M'), $end);
		foreach($daterange as $date):
			$tests[$date->format('m')][$date->format('d')][$date->format('H:i')] = $date->format('H') * 3600 + $date->format('i')*60;
		endforeach;

		// Endzeit muss manuell eingefügt werden
//		$tests[$date->format('m')][$date->format('d')][$end->format('H:i')] = $end->format('H') * 3600 + $end->format('i') * 60;
	endforeach;

	$tests = array_filter($tests);
	//pr($tests);
	if(!empty($tests)) {
		// Zeiten nach Unterbrechungen trennen
		foreach($tests as $month=>$days) {
			$max = 0; 													// Maximale größe eines Arrays
			$tmp = array();
			
			$graph = clone $Graph;
			
			foreach($days as $day=>$times){
				$idx = 0;
				$lastTime = null;
				foreach($times as $time=>$v) {
					if($lastTime != null && abs($v-$lastTime) > 300) {	// Bei mehr als 5 Minuten Abstand zum letzten Wert neuen Abschnitt beginnen
						$lastTime = null;
						ksort($tmp[$day][$idx++]);
						$tmp[$day][$idx][$time] = $v;
					} else {
						$lastTime = $v;
						$tmp[$day][$idx][$time] = $v;
					}
				}
				
				$max = max($max, count($tmp[$day]));

				// Zeiten nach erstem Eintrag sortieren
				usort($tmp[$day], function($a, $b) {$a = reset(array_keys($a)); $b = reset(array_keys($b)); return $a > $b ? 1 : -1;});
	
				// Zeit in Stunden und Stundenbruchteile umwandeln
				$tmp[$day] = array_map(function($elem) {return array(reset($elem)/3600.0, end($elem)/3600.0);}, $tmp[$day]);
			}

			// Template erzeugen
			$tpl = array();
			for($i=0; $i<$max; $i++) {$tpl[$i] = array(null, null);}
			
			// Datenarray mit Template auffüllen, bis jedes Element gleich viele Teile hat
			$tests[$month] = array_map(
				function($elem) use($tpl) {
					$ret = is_array($elem) ? $elem+$tpl : $tpl;
					
					// Zeiten nach erstem Eintrag sortieren
					usort($ret, function($a, $b) {return reset($a) > reset($b) ? 1 : -1;});
					
					return $ret;
				},
				$tmp + array_flip(range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)))
			);
			ksort($tests[$month]);

			for($i=0; $i<$max; $i++)
			{
				$data = array_map(function($elem) use($i) {return $elem[$i];}, $tests[$month]);

				$plot = array();
				foreach($data as $time) {
					array_push($plot, $time[0], $time[1], $time[0], $time[1]);
				}
				
				//pr($plot);
				$worktime = new StockPlot($plot);
				$worktime->SetWidth(28);
				$worktime->SetColor($border, $color);
				
				$worktime->SetCenter();
		
				$graph->Add($worktime);
			}
		
			/*
			ob_start();
			$graph->Stroke();
			$graphs[intval($month)] = $graph = base64_encode(ob_get_contents());
			ob_end_clean();
			*/
			$graphs[intval($month)] = $graph->Stroke(_IMG_HANDLER);
		}
	}

	//pr($graphs);
	ksort($graphs);
	
	if(!isset($preventRender)) {
		if(count($graphs) == 0) {
			echo $this->Html->tag('p', __('No data to display'));
		} elseif(count($graphs) == 1) {
			ob_start();
			imagepng(reset($graphs));
			$g = base64_encode(ob_get_contents());
			ob_end_clean();
			echo '<img src="data:image/png;base64,'.$g.'" />';
		} else {
			foreach($graphs as $month=>$graph) {
				ob_start();
				imagepng($graph);
				$g = base64_encode(ob_get_contents());
				ob_end_clean();

				$date = new DateTime();
				$date->setDate($date->format('Y'), $month, 1);
				echo '<div><h3>'.(__($date->format('F')).' '.$date->format('Y')).'</h3><img src="data:image/png;base64,'.$g.'" /></div>';
			}
		}
	} else {
		foreach($graphs as &$graph) {
			ob_start();
			imagepng($graph);
			$graph = array(imagesx($graph), imagesy($graph), base64_encode(ob_get_contents()));
			ob_end_clean();
		}
		echo json_encode($graphs);
	}
?>