<div class="modalarea">
<h2><?php echo __('Add specialcharacter');?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->Form->create('Specialcharacter', array('class' => 'login')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('value');
		echo $this->Form->input('discription');
	?>
	</fieldset>
<?php
echo $this->Form->end(__('Submit', true));
?>
</div>
<div class="clear" id="testdiv"></div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/ajax_modal_request');
?>
