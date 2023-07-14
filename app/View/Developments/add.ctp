<div class="modalarea">
<h2><?php echo __('Add progress'); ?></h2>

<?php echo $this->Form->create('Development',array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('name');
		echo '</fieldset><fieldset>';
		echo $this->Form->input('discription');
		echo '</fieldset><fieldset>';
		echo $this->Form->input('Testingcomp');
	?>
	</fieldset>
<?php
echo $this->Form->end(__('Submit', true));
?>
</div>
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){
	echo $afterEDIT;
	echo $this->JqueryScripte->DialogClose(); 
	}
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>


