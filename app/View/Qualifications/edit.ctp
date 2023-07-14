<div class="modalarea qualifications form">
<h2><?php echo __('Edit Qualification'); ?></h2>
<?php echo $this->Form->create('Qualification', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('user_id');
		echo $this->Form->input('examiner-id');
		echo $this->Form->input('certification-number');
		echo $this->Form->input('testingmethod_id');
		echo $this->Form->input('level');
		echo $this->Form->input('timeperiod');
		echo $this->Form->input('testingcomp_id');
		echo $this->Form->input('supervisors');
	?>
	</fieldset>
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>        
<?php 
echo $this->Form->end(__('Submit', true));
?>
</div>
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>