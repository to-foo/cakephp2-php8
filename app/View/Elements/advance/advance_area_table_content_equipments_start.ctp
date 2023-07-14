<?php
	if(!isset($_value['status'])) return;
	if(!isset($_value['schedule']['status'])) return;

	if($_value['schedule']['status']['end'] == 0) $open_class = 'counter_open';
	if($_value['schedule']['status']['end'] == 1) $open_class = 'counter_open_delay';

	if(!isset($_value['Status'])) return;

	foreach($_value['Status'] as $__key => $__value){

		if($__key == 'all_advance_methods') continue;

		echo '<tr>';
		echo '<td>';
//		echo __($__key,true);
		echo $this->element('advance/statistic/advance_cascadegroup_statistic_detail_single',array('key' => __($__key,true),'value' => $__value));
		echo '</td>';

		if($__value['result']['open'] > 0) echo '<td><span class="counter ' . $open_class . '">' . $__value['result']['open'] . '/' . $__value['result']['count'] . '</span></td>';
		else echo '<td><span class="counter">' . $__value['result']['open'] . '</span></td>';

		if($__value['result']['okay'] > 0)  echo '<td><span class="counter counter_okay">' . $__value['result']['okay'] . '/' . $__value['result']['count'] . '</span></td>';
		else echo '<td><span class="counter">' . $__value['result']['okay'] . '</span></td>';

		if($__value['result']['error'] > 0) echo '<td><span class="counter counter_error">' . $__value['result']['error'] . '/' . $__value['result']['count'] . '</span></td>';
		else echo '<td><span class="counter">' . $__value['result']['error'] . '</span></td>';

		echo '</tr>';
	}

?>
