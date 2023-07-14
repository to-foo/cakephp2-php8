<?php
echo $this->Form->create('Order', array('class' => 'dropzone'));
echo $this->Form->input('ajax_true', array('name' => 'ajax_true','type' => 'hidden', 'value' => 1));
echo $this->Form->input('uuid', array('name' => 'uuid','type' => 'hidden'));

echo '<div class="submit clear">';
echo '<button type="submit" id="UploadFormSendButton" class="btn btn-primary">' . __('Submit',true) . '</button>';
echo '</div>';
echo $this->Form->end();
?>
