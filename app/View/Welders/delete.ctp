<div class="modalarea welders index inhalt">
<h2><?php echo __('Delete Welder'); ?></h2>
<div class="hint"><p>
<?php echo __('Will you delete the welder',true);?>
<b>
<?php echo ($this->request->data['Welder']['name']);?>
</b>
 ?
</p>
</div>
<?php if(isset($FormName) && count($FormName) > 0){
  echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>
<?php echo $this->Form->create('Welder', array('class' => 'login')); ?>
<?php echo $this->Form->input('id');?>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Delete',true)));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'WelderDeleteForm'));
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
 ?>
