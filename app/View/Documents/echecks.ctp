<div class="modalarea examiners index inhalt">
	<h2><?php echo __('Documents'); ?> - <?php echo $document['DocumentTestingmethod'][0]['verfahren'];?> - <?php echo $document['Document']['name'];?> </h2>
<div class="quicksearch">
<?php //  echo $this->Navigation->quickExaminerSearching('quicksearch',2,__('Examiner name', true),true); ?>
</div>
<?php echo $this->element('Flash/_messages');?>
</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
