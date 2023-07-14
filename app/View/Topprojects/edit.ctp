<div class="modalarea">
<!-- Beginn Headline -->
<h2><?php echo __('Edit topproject'); ?></h2>
<!-- Ende Headline -->
<!-- Beginn Message -->
<?php echo $this->element('Flash/_messages');?>
<!-- Ende Message -->
<?php if(isset($FormName) && count($FormName) > 0) echo $this->element('js/reload_container');?>
<?php echo $this->Form->create('Topproject', array('class' => 'login')); ?>
<fieldset>
<?php
echo $this->Form->input('id');
echo $this->Form->input('projektname');
echo $this->Form->input('identification');

echo $this->Form->input('add_master_dropdowns', array(
    'options' => array('1' => __('Add master dropdown values'), '0' => __('Remove master dropdown values')),
    'type' => 'radio',
));

echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Report',array('multiple' => true,'label' => __('Involved reports',true),'selected' => $this->request->data['Report']['selected']));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingcomp',array('multiple' => true,'label' => __('Involved companies',true),'selected' => $this->request->data['Testingcomp']['selected']));
echo '</fieldset>';
/*
echo '<fieldset class="multiple_field">';
if(isset($this->request->data['Dropdownsmaster'])) echo $this->Form->input('Dropdownsmaster',array('multiple' => true,'label' => __('Involved dropdowns',true),'selected' => $this->request->data['Dropdownsmaster']['selected']));
echo '</fieldset>';
*/
echo '<fieldset>';
echo $this->Form->input('projektbeschreibung');
echo $this->Form->input('status',array('type' => 'hidden'));
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Submit',true)));?>
</div>
<?php
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
echo $this->element('js/form_send_modal',array('FormId' => 'TopprojectEditForm'));
?>
