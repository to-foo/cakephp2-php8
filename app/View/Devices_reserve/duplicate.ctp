<div class="modalarea examiners index inhalt">
<h2><?php echo __('Duplicate device');?></h2>
<div class="quicksearch">
<?php if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_device',array('target_id' => 'id','targedaction' => 'view','action' => 'quicksearch','minLength' => 2,'discription' => __('Intern number', true)));?>
</div>
<div class="hint"><p>
<?php echo __('Do you want to duplicate this device?',true);?>
</p><p><b>
<?php echo $device['Device']['name'];?>
</b>
</p></div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->Form->create('Device', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->end(__('Duplicate', true));?>

</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
