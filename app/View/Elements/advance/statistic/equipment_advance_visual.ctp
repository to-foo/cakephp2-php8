<?php
if(!isset($value['statistic'])) return;
if(!isset($this->request->data['Status'][$key])) return;

echo $this->Form->input('advance_' . $key,array('class' => 'advance_line','type' => 'hidden','value' => $value['statistic']['advance_all_percent'] . '%'));
if($value['error'] > 0) echo $this->Form->input('advance_color_' . $key,array('class' => 'advance_color_line','type' => 'hidden','value' => '#ff0000'));;
if($value['error'] == 0) echo $this->Form->input('advance_color_' . $key,array('class' => 'advance_color_line','type' => 'hidden','value' => $this->request->data['Status'][$key]['advance_line_color']));;

echo '<div class="advance ' . $class . '">';
echo '<div class="this_advance advance_' . $key . ' advance_color_' . $key . '"></div>';
echo '</div>';
?>
