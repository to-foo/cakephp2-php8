<div class="modalarea detail">
<h2><?php echo __('Add expediting'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->element('expediting/add_expediting_step_1');?>
</div>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingAddForm'));
 ?>
