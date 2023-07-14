<div class="modalarea">
	<h2><?php echo __('Delete file'); ?></h2>
	<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
	<?php
	if(isset($FormName)){
		echo $this->element('js/ajax_stop_loader');
		echo $this->element('js/reload_container',array('FormName' => $FormName));
		echo '</div>';
		return;
	}
	?>
	<div class="hint"><p>
	<?php echo __('Do you want to delete this file?',true);?>
	</p></div>
	<?php echo $this->Form->create('Reportfile', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php echo $this->Form->input('id',array());?>
	</fieldset>
	<?php echo $this->Form->end(__('Delete')); ?>

</div>
<?php
echo $this->element('js/ajax_send_modal_form');
?>
