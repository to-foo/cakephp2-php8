<div class="modalarea">
<h2><?php echo __('Delete specialcharacters'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->Form->create('Specialcharacter', array('class' => 'login')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
	?>
	</fieldset>
<?php
echo $this->Form->end(__('Delete', true));
?>

</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/ajax_modal_request');
?>
