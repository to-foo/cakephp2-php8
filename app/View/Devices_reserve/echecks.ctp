<div class="modalarea examiners index inhalt">
	<h2><?php echo __('Devices'); ?> - <?php echo $device['DeviceTestingmethod'][0]['verfahren'];?> - <?php echo $device['Device']['name'];?> </h2>
<div class="quicksearch">
<?php //  echo $this->Navigation->quickExaminerSearching('quicksearch',2,__('Examiner name', true),true); ?>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?> 
