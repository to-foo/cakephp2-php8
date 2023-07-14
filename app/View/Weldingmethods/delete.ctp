<div class="modalarea weldingmethods form">
<h2><?php echo __('Delete Weldingmethod'); ?></h2>
<div class="hint"><p>
<?php echo __('Will you delete the welding method?',true);?> 

</p>
</div>

<?php echo $this->Form->create('Weldingmethod', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->end(__('Delete',true)); ?>

</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
