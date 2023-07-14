<?php
if(!isset($value['result'])) return;
if(count($value['result']) == 0) return;

echo '<div class="div_plot_container">';

	echo '<div class="table_result">';
	echo $key;
	echo ' (' . round($value['result']['advance_all_percent'],2) . '%)';
	echo '<div class="diagramm_outer">';
	echo '<div class="diagramm_inner" style="background-color: '.$value['advance_line_color'].';width: '.$value['result']['advance_all_percent'].'px">';
	echo '</div>';
	echo '</div>';
	echo '</div>';

echo '</div>';

?>
