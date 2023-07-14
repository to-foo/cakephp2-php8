<?php
if(!isset($_value['Status'])) return;
echo '<tr>';
echo '<td class="collaps">';
echo $this->element('advance/statistic/advance_cascadegroup_statistic_detail',array('value' => $_value));
echo '</td>';
echo '</tr>';
?>
