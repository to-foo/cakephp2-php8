<div class="modalarea devicetestingmethods form">
<h2><?php echo __('Delete DeviceTestingmethod'); ?></h2>
<div class="hint"><p>
<?php echo __('Will you delete the devicetestingmethod',true);?> 
<b>
<?php echo ($this->request->data['DeviceTestingmethod']['verfahren']);?>
</b>
 ?
</p>
</div>
<?php echo $this->Form->create('DeviceTestingmethod', array('class' => 'modal dialogform')); ?>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->end(__('Delete',true)); ?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
