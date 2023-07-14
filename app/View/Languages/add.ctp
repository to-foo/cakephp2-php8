<div class="languages form">
<?php echo $this->Form->create('Language'); ?>
	<fieldset>
		<legend><?php echo __('Add language'); ?></legend>
	<?php
		echo $this->Form->input('locale');
		echo $this->Form->input('beschreibung');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List languages'), array('action' => 'index')); ?></li>
	</ul>
</div>
