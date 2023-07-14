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

		echo '<div class="normal"><img class="" src="data:image/png;base64, ' . $value  . ' " /></div>';
	}
	echo '</div>';
}
echo '</div>';

echo '<div class="advance_diagrammcontent">';
echo '<table class="advancetool">';

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
	echo '<span class="large">' . round($_value['status']['all_advance_methods']['advance_all_percent'],2) . '%</span>';
	echo '</div>';

	if(isset($_value['complete']) && $_value['complete'] == 1){
		echo '<div class="table_result icon">';
		echo __('Status',true);
		echo '<div class="icon_item okay"></div>';
		echo '</div>';
		echo '</div>';
	}

	if($_value['schedule']['status']['end_delay'] == 0 && $_value['schedule']['status']['end'] == 1 && $_value['complete'] == 0) {
		echo '<div class="table_result icon">';
		echo __('Status',true);
		echo '<div class="icon_item delay"></div>';
		echo '</div>';
		echo '</div>';
	}
	if($_value['schedule']['status']['end_delay'] == 1 && $_value['schedule']['status']['end'] == 1 && $_value['complete'] == 0) {
		echo '<div class="table_result icon">';
		echo __('Status',true);
		echo '<div class="icon_item tolate"></div>';
		echo '</div>';
		echo '</div>';
	}
	if($_value['schedule']['status']['end_delay'] == 0 && $_value['schedule']['status']['end'] == 0 && $_value['complete'] == 0) {
		echo '<div class="table_result icon">';
		echo __('Status',true);
		echo '<div class="icon_item intime"></div>';
		echo '</div>';
		echo '</div>';
	}


} else {
	echo __('No data available',true);
}

echo '</td>';
echo '<td>';

if($_value['status']['all_advance_methods']['count'] > 0){
	echo  '<span class="counter counter_open">' . $_value['Status']['all_advance_methods']['result']['open'] . '/' . $_value['Status']['all_advance_methods']['result']['count'] . '</span>';
}
echo '</td>';
echo '<td>';
if($_value['status']['all_advance_methods']['okay'] > 0){
	echo  '<span class="counter counter_okay">' . $_value['Status']['all_advance_methods']['result']['okay'] . '/' . $_value['Status']['all_advance_methods']['result']['count'] . '</span>';
}
echo '</td>';
echo '<td>';
if($_value['status']['all_advance_methods']['error'] > 0){
	echo  '<span class="counter counter_error">' . $_value['Status']['all_advance_methods']['result']['error'] . '/' . $_value['Status']['all_advance_methods']['result']['count'] . '</span>';
}
echo '</td>';
echo '</tr>';

if(isset($_value['complete']) && $_value['complete'] == 1) continue;

if(isset($_value['status']) && count($_value['status']) == 0){
	echo '<tr>';
	echo '<td colspan="4">' . __('no advances available',true) . '</td>';
	echo '</tr>';
}

echo $this->element('advance/advance_area_table_content_equipments_start',array('_value' => $_value));
//echo $this->element('advance/advance_area_table_content_cascades',array('_value' => $_value));

}

echo '</table>';
echo '</div>';
echo '</div>';
?>
