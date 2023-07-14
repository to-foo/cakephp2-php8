<?php
echo '<div id="advance_tablecontent" class="hint advance_content advance_tablecontent">';

if(!isset($this->request->data['Scheme'])){

	echo '<div class="hint">';
	echo '<p>' . __('No advances available.') . '</p>';
	echo '</div>';
	echo '</div>';
	return;
}


echo '<div class="hint advance_diagrammcontent advance_statistic">';

echo $this->element('advance/advance_cascadegroup_schedule');

if(isset($this->request->data['RingPlotDiagramm'])){
	echo '<div class="div_plot_container">';

	foreach ($this->request->data['RingPlotDiagramm'] as $key => $value){

		if($key == 'all_advance_methods') continue;
		if(empty($value)) continue;

		echo '<div class="normal"><img class="" src="data:image/png;base64, ' . $value  . ' " /></div>';
	}
	echo '</div>';
}
echo '</div>';

echo '<div class="advance_diagrammcontent">';
echo '<table class="advancetool">';

if(!isset($this->request->data['Scheme']['children'])){

	echo '</table>';
	echo '</div>';
	echo '</div>';
	return;
}

foreach ($this->request->data['Scheme']['children'] as $_key => $_value) {

if(!isset($_value['complete'])) $_value['complete'] = 0;

echo '<tr>';
echo '<th>';
echo $this->Html->link($_value['discription'],array_merge(array('action' => 'json_scheme'),$this->request->projectvars['VarsArray']),array('rev' => $_key,'rel' => 'advance_area','title' => $_value['discription'],'class' => 'round blank'));
echo'</th>';

if(isset($_value['status'])){
	echo '<th>' . __('not started',true) . '</th>';
	echo '<th>' . __('completed',true) . '</th>';
	echo '<th>' . __('repair',true) . '</th>';
}
echo '</tr>';

echo '<tr>';
echo '<td>';

if(isset($_value['schedule']['start'])){
	echo '<div class="div_plot_container">';
	echo '<div class="table_result">';
	echo $_value['schedule']['start'] . ' -> ' . $_value['schedule']['end_delay'];
	echo '<div class="diagramm_outer">';
	echo '<div class="diagramm_inner" style="background-color: ' . $_value['schedule']['status_color'] . ';width: ' . $_value['schedule']['interval_percent'] . 'px">';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="table_result">';
	echo __('Progress',true);
	if(isset($_value['Status']['all_advance_methods']['result']['advance_all_percent'])) echo '<span class="large">' . $_value['Status']['all_advance_methods']['result']['advance_all_percent'] . '%</span>';
	else echo '<span class="large">' . round($_value['status']['all_advance_methods']['advance_all_percent'],2) . '%</span>';
	echo '</div>';

	echo '<div class="table_result icon">';
	echo __('Status',true);
	echo '<div title="' . $_value['schedule']['status_text'] . '" class="icon_item ' . $_value['schedule']['status_class'] . '"></div>';
	echo '</div>';
	echo '</div>';


} else {
	echo __('No data available',true);
}

echo '</td>';
echo '<td>';

if(isset($_value['schedule']['status'])){
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

	if($_value['status']['all_advance_methods']['open'] > 0){
		echo  '<span title ="' . $status_text . '" class="counter counter_' . $status_class . '">' . $_value['status']['all_advance_methods']['open'] . '/' . $_value['status']['all_advance_methods']['count'] . '</span>';
	}
}
echo '</td>';
echo '<td>';

if($_value['status']['all_advance_methods']['okay'] > 0){
	echo  '<span class="counter counter_okay">' . $_value['status']['all_advance_methods']['okay'] . '/' . $_value['status']['all_advance_methods']['count'] . '</span>';
}

echo '</td>';
echo '<td>';

if($_value['status']['all_advance_methods']['error'] > 0){
	echo  '<span class="counter counter_error">' . $_value['status']['all_advance_methods']['error'] . '/' . $_value['status']['all_advance_methods']['count'] . '</span>';
}

echo '</td>';
echo '</tr>';

if(isset($_value['complete']) && $_value['complete'] == 1) continue;

if(isset($_value['status']) && count($_value['status']) == 0){
	echo '<tr>';
	echo '<td colspan="4">' . __('no advances available',true) . '</td>';
	echo '</tr>';
}

echo $this->element('advance/advance_area_table_content_equipments',array('_value' => $_value));
echo $this->element('advance/advance_area_table_content_cascades',array('_value' => $_value));

}

echo '</table>';
echo '</div>';
echo '</div>';
?>
