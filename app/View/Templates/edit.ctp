<div class="inhalt testinginstructions form">
<h2><?php echo __('Edit template'); ?></h2>
<div class="quicksearch">
</div>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->element('templates/edit_master_menue');?>
<div class="areas" id="main_area">
<?php

echo $this->Form->create('Template',array('class' => 'login'));
echo '<fieldset>';
echo $this->Form->input('save_desctiption',array('type' => 'hidden','value' => 1));
echo $this->Form->input('id');
echo $this->Form->input('name');
echo $this->Form->input('description');
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingcomp',array('multiple' => true,'label' => __('Involved companies',true),'selected' => $this->request->data['Testingcomp']['selected']));
echo '</fieldset>';
echo $this->Form->end(__('Submit'));
?>
</div>
<?php
echo $this->element('templates/edit_template_data_areas');
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send',array('FormId' => 'TemplateEditForm'));
echo $this->element('js/form_send',array('FormId' => 'TemplateDataEditForm'));
echo $this->element('js/form_send_by_link');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
