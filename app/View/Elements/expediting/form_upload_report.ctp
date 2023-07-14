<?php
echo $this->Form->create('Order', array('class' => 'dropzone login'));
echo $this->Form->input('ajax_true', array('name' => 'ajax_true','type' => 'hidden', 'value' => 1));
echo '<fieldset>';
echo $this->Form->input('ExpeditingType', array(
  'name' => 'ExpeditingType',
  'options' => $Supplier['ExpeditingTypes'],
  'empty' => __('Choose expediting step',true)
  )
);
echo '</fieldset>';
echo '<div class="submit clear">';
echo '<button type="submit" id="UploadFormSendButton" class="btn btn-primary">' . __('Submit',true) . '</button>';
echo '</div>';
echo $this->Form->end();
echo $this->element('expediting/js/expediting_type_change');
?>
