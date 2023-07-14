<?php

$Status = array('0' => __('active',true), '1' => __('deactiv',true));

echo '<div class="areas" id="data_area">';
echo '<h3> ';
echo __('Allgemeine/Prüfdaten',true) . ' - ' . $this->request->data['Template']['name'];
echo '</h3>';
echo $this->Form->create('TemplateData',array('class' => 'login flex'));
echo '<fieldset class="flex">';
echo $this->Form->input('edit_template_fields',array('id' => 'edit_template_fields','name' => 'edit_template_fields','type' => 'hidden','value' => 1));
echo '</fieldset>';
echo '<h4 >' . __('Edit General Infos',true) . '</h4>';
echo '<div class="flex">';
if(isset($this->request->data['Template']['data'][$DataKeys[0]])) echo $this->element('templates/reportform',array('data' => $this->request->data['Template']['data'],'setting' => $this->request->data['Template']['settings'][$DataKeys[0]],'lang' => $locale,'step' => 'General'));
echo '</div>';
echo '<h4>' . __('Edit Specify infos',true) . '</h4>';
echo '<div class="flex">';
if(isset($this->request->data['Template']['data'][$DataKeys[1]])) echo $this->element('templates/reportform',array('data' => $this->request->data['Template']['data'],'setting' => $this->request->data['Template']['settings'][$DataKeys[1]],'lang' => $locale,'step' => 'General'));
echo '</div>';
echo $this->Form->end(__('Submit'));
echo '</div>';

echo '<div class="areas" id="evaluation_area">';
echo '<h3> ';
echo __('Auswertungsdaten',true) . ' - ' . $this->request->data['Template']['name'];
echo '</h3>';
echo $this->element('templates/evaluations');
echo '</div>';
echo $this->element('templates/js/edit_template');
echo $this->element('templates/js/edit_attentions');
?>
