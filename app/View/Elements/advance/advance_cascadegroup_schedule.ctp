<?php
if(!isset($this->request->data['RingPlotDiagramm'])) return;


echo '<h6>' . $this->request->data['CascadeGroupTitle'] . '</h6>';

echo '<div class="dates">';

echo '<div class="date">';

echo '<div class="group group_large">';
echo '<div class="div_plot_container">';
foreach ($this->request->data['RingPlotDiagramm'] as $key => $value){

	if($key != 'all_advance_methods') continue;
	if(empty($value)) continue;

	echo '<div class="single_summary"><img class="" src="data:image/png;base64, ' . $value  . ' " /></div>';
}
echo '</div>';
echo '</div>';

echo '<div class="group group_large">';

echo '<div class="item">';
echo '<div class="descriptions">';
echo __('Start date');
echo '</div>';
echo '<div class="value">';
echo $this->request->data['AdvancesCascade']['start'];
echo '</div>';
echo '</div>';

echo '<div class="item">';
echo '<div class="descriptions">';
echo __('End date');
echo '</div>';
echo '<div class="value">';
echo $this->request->data['AdvancesCascade']['end'];
echo '</div>';
echo '</div>';

echo '<div class="item">';
echo '<div class="descriptions">';
echo __('planned');
echo '</div>';
echo '<div class="value">';
echo $this->request->data['AdvancesCascade']['interval'];
echo '</div>';
echo '</div>';

echo '<div class="item">';
echo '<div class="descriptions">';
echo __('karenz');
echo '</div>';
echo '<div class="value">';
echo $this->request->data['AdvancesCascade']['karenz'];
echo '</div>';
echo '</div>';

echo '<div class="item ausnahme">';
echo $this->element('advance/advance_cascadegroup_schedule_status');
echo '</div>';

echo '</div>';
echo '</div>';
echo '</div>';

?>
