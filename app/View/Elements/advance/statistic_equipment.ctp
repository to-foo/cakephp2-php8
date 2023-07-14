<?php
if(!isset($this->request->data['Scheme']['AdvancesOrder']['Date'])) return;

echo '<div class="advance_statistic hint">';

echo '<div class="advance_diagrammcontent">';

$TimeStatus = null;

if(isset($this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] ) && $this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] == 1 && $this->request->data['Status']['all_advance_methods']['result']['advance_rep_deci'] == 0){
  $TimeStatus = '<p class="time_status success">';
  $TimeStatus .= __('Work on this area is complete',true);
  $TimeStatus .= '</p>';
}

if(isset($this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] ) && $this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] == 1 && $this->request->data['Status']['all_advance_methods']['result']['advance_rep_deci'] > 0){
  $TimeStatus = '<p class="time_status error">';
  $TimeStatus .= __('Work on this area is complete with repairs',true);
  $TimeStatus .= '</p>';
}

foreach ($this->request->data['Scheme']['AdvancesOrder']['Date']['status'] as $key => $value) {

  if(isset($this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] ) && $this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] == 1) continue;

  if($key == 'end_delay' && $value == 1){
    $TimeStatus = '<p class="time_status error">';
    $TimeStatus .= __('The work on this area is delayed',true);
    $TimeStatus .= '</p>';
    break;
  }

  if($key == 'end' && $value == 1){
    $TimeStatus = '<p class="time_status delay">';
    $TimeStatus .= __('The work on this area is delayed',true);
    $TimeStatus .= '</p>';
    break;
  }

  if($key == 'start' && $value == 0){
    $TimeStatus = '<p class="time_status future">';
    $TimeStatus .= __('Work on this area will start on',true) . ' ' . $this->request->data['AdvancesCascade']['start'];
    $TimeStatus .= '</p>';
    break;
  }

  if($key == 'start' && $value == 1){
    $TimeStatus = '<p class="time_status intime">';
    $TimeStatus .= __('Work on this area began on',true) . ' ' . $this->request->data['AdvancesCascade']['start'];
    $TimeStatus .= '</p>';
  }

}

echo $TimeStatus;
?>

</div>

<div class="statistic_dates">
<?php
echo '<div class="group">';
  echo '<div class="item">';
    echo '<div class="descriptions">';
      echo __('Start date');
    echo '</div>';
    echo '<div class="value">';
      echo $this->request->data['Scheme']['AdvancesOrder']['Date']['start'];
    echo '</div>';
  echo '</div>';

  echo '<div class="item">';
    echo '<div class="descriptions">';
      echo __('End date');
    echo '</div>';
    echo '<div class="value">';
      echo $this->request->data['Scheme']['AdvancesOrder']['Date']['end'];
    echo '</div>';
  echo '</div>';

  echo '<div class="advance">';
    echo '<div class="this_advance statistic_days_gone"></div>';
  echo '</div>';

echo '</div>';
?>
</div>

<div class="statistic_dates">
<?php
  foreach ($this->request->data['Scheme']['AdvancesOrder']['AdvancesDataTypes'] as $key => $value) {

    if($value['count'] == 0) continue;

    echo $this->Form->input('advance_count_' . $key,array('type' => 'hidden','value' => $value['count']));
    echo $this->Form->input('advance_open_' . $key,array('type' => 'hidden','value' => $value['open']));
    echo $this->Form->input('advance_okay_' . $key,array('type' => 'hidden','value' => $value['okay']));
    echo $this->Form->input('advance_error_' . $key,array('type' => 'hidden','value' => $value['error']));

    echo '<div class="group group_advance_' . $key . '">';
    echo $this->element('advance/statistic/equipment_advance_element',array('class' => '','key' => __('Type'),'value' => __($key,true)));
    echo $this->element('advance/statistic/equipment_advance_element',array('class' => 'advance_count_' . $key,'key' => __('Count'),'value' => $value['count']));
    echo $this->element('advance/statistic/equipment_advance_element',array('class' => 'advance_open_' . $key,'key' => __('Open'),'value' => $value['open']));
    echo $this->element('advance/statistic/equipment_advance_element',array('class' => 'advance_okay_' . $key,'key' => __('Okay'),'value' => $value['okay']));
    echo $this->element('advance/statistic/equipment_advance_element',array('class' => 'advance_error_' . $key,'key' => __('Error'),'value' => $value['error']));
    echo $this->element('advance/statistic/equipment_advance_visual',array('class' => 'advance_line_' . $key,'key' => $key,'value' => $value));
    echo '</div>';
}
?>
</div>
