<div class="modalarea examiners index inhalt">
<h2><?php echo __('Duplicate document');?></h2>
<div class="quicksearch">
<?php
if(isset($ControllerQuickSearch)){
	echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,false);
}
?>
</div>
<div class="hint"><p>
<?php echo __('Do you want to duplicate this document?',true);?>
</p><p><b>
<?php echo $document['Document']['name'];?>
</b>
</p></div>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->Form->create('Document', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->end(__('Duplicate', true));?>

</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
