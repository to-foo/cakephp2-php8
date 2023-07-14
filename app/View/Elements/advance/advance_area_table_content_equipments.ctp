<?php
	if(!isset($_value['status'])) return;
	if(!isset($_value['schedule']['status'])) return;

	$status_text = '';
	$status_class = 'future';
	if($_value['schedule']['status']['start'] == 0){
		$status_text = __('Work on this area will start on',true) . ' ' . $_value['schedule']['start'];
		$status_class = 'future';
	}
	if($_value['schedule']['status']['start'] == 1){
		$status_text = __('Work on this area began on',true) . ' ' . $_value['schedule']['start'];
		$status_class = 'open';
	}
	if($_value['schedule']['status']['end'] == 1){
		$status_text = __('The work on this area is delayed',true);
		$status_class = 'open_delay';
	}
	if($_value['schedule']['status']['end_delay'] == 1){
		$status_text = __('The work on this area is delayed',true);
		$status_class = 'error';
	}


	foreach($_value['status'] as $__key => $__value){

		if($__key == 'all_advance_methods') continue;

		echo '<tr>';
		echo '<td>' . __($__key,true) . '</td>';

		if($__value['open'] > 0) echo '<td><span title="' . $status_text . '" class="counter counter_' . $status_class . '">' . $__value['open'] . '/' . $__value['count'] . '</span></td>';
		else echo '<td><span class="counter">' . $__value['open'] . '</span></td>';

		if($__value['okay'] > 0)  echo '<td><span class="counter counter_okay">' . $__value['okay'] . '/' . $__value['count'] . '</span></td>';
		else echo '<td><span class="counter">' . $__value['okay'] . '</span></td>';

		if($__value['error'] > 0) echo '<td><span class="counter counter_error">' . $__value['error'] . '/' . $__value['count'] . '</span></td>';
		else echo '<td><span class="counter">' . $__value['error'] . '</span></td>';

		echo '</tr>';
	}
?>
