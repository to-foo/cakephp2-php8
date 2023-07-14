<?php
echo '<div id="advance_tablecontent" class="advance_content advance_tablecontent">';
echo '<div class="advance_diagrammcontent">';

echo '<div class="advance_statistic">';

echo $this->element('advance/advance_cascadegroup_schedule');

if(isset($this->request->data['RingPlotDiagramm'])){
	echo '<div class="div_plot_container">';
	foreach ($this->request->data['RingPlotDiagramm'] as $key => $value) {

		if($key == 'all_advance_methods') continue;

		echo '<div class="normal"><img class="" src="data:image/png;base64, ' . $value  . ' " /></div>';
	}

	echo '</div>';
}
echo '</div>';

echo '<table class="advancetool">';

echo '<tr>';
echo '<th>' . __('Description',true) . '</th>';
echo '</tr>';

foreach ($this->request->data['Scheme']['CascadeGroup'] as $key => $value) {

	if(count($value['Cascades']) == 0) continue;

	echo '<tr>';
	echo '<td class="collaps">';
	$Count = 0;
	if(isset($value['AdvancesCascade'])) $Count = count($value['AdvancesCascade']);
	echo $this->Html->link($value['Description'] . ' (' . $Count  . ')',array_merge(array('action' => 'json_scheme'),$this->request->projectvars['VarsArray']),array('rel' => $key,'class' => 'round cascade_group'));
	echo $this->element('advance/statistic/advance_cascadegroup_status_detail',array('value' => $value));
	echo $this->element('advance/statistic/advance_cascadegroup_statistic_detail',array('value' => $value));
	echo '</td>';
	echo '</tr>';

}

echo '</table>';
echo '</div>';
?>
