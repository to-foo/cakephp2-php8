<?php
if(!isset($value['Status'])) return;
if(count($value['Status']) == 0) return;

echo '<div class="div_plot_container">';

foreach ($value['Status'] as $key => $value) {

	if($key == 'all_advance_methods') continue;

	echo '<div class="table_result">';
	echo __($key,true) . ' ';
	echo '(' . round($value['result']['advance_all_percent'],2) . '%)';
	echo '<div class="diagramm_outer">';
	echo '<div class="diagramm_inner" style="background-color: '.$value['advance_line_color'].';width: '.$value['result']['advance_all_percent'].'px">';
	echo '</div>';
	echo '</div>';
	echo '</div>';

}
echo '</div>';

?>
