<?php
if(!isset($this->request->data['TicketData'])) return;
if(empty($this->request->data['TicketData'])) return;

foreach ($this->request->data['TicketData'] as $key => $value) {

  echo '<div class="flex_info">';
  echo $this->element('infos/flex_info',array('value' => $value['sheet_no'],'label' => __('Sheet No.',true)));
  echo $this->element('infos/flex_info',array('value' => $value['task_id'],'label' => __('Task Id',true)));
  echo $this->element('infos/flex_info',array('value' => $value['spool_id'],'label' => __('Spool Id',true)));
  echo '</div>';

  if(!isset($value['json_decode'])) continue;
  if(empty($value['json_decode'])) continue;
  if(!isset($value['json_decode']['data'])) continue;
  if(empty($value['json_decode']['data'])) continue;

  echo '<div class="flex_info">';

  foreach ($value['json_decode']['data']['akz_data'] as $_key => $_value) {
    echo $this->element('infos/flex_info',array('value' => $_value,'label' => Inflector::humanize($_key)));
  }
  echo '</div>';

}
?>
