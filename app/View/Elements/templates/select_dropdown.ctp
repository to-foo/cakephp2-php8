<?php
if(!isset($this->request->data['Reportnumber']['status'])) return;
if($this->request->data['Reportnumber']['status'] > 0) return;
if(Configure::read('TemplateManagement') == false) return;
if(!isset($this->request->data['Templates'])) return;
if(count($this->request->data['Templates']) == 0) return;

echo '<select name="TemplateDropdownSelect" id="TemplateDropdownSelect" style="width:13em">';
echo '<option value="0" title="">Select a template</option>';

foreach ($this->request->data['Templates'] as $key => $value) {
  echo '<option value="' . $value['Template']['id'] . '" title="' . $value['Template']['description'] . '">' . $value['Template']['name'] . '</option>';
}
echo '</select>';

echo $this->element('templates/js/select_dropdown');
?>
