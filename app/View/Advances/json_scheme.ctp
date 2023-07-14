<?php
echo $this->element('js/ajax_link');
echo $this->Form->input('StartId',array('type' => 'hidden','value' => $StartId));

echo $this->element('Flash/_messages');

echo $this->element('advance/js/schema_menue_js');
echo $this->element('js/json_request_animation');

if(isset($this->request->data['Scheme']['AdvancesOrder'])){
  echo $this->element('advance/advance_orders');
  return;
}
if(isset($this->request->data['Scheme']['CascadeGroup'])){
  echo $this->element('advance/advance_cascadegroup');
  return;
}
echo $this->element('advance/advance_area');
?>
