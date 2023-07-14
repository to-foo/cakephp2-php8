#<div class="modalarea examiners index inhalt">
	<h2><?php echo __('Devices'); ?> - <?php echo $device['DeviceTestingmethod']['verfahren'];?> - <?php echo $device['Device']['name'];?> </h2>
<div class="quicksearch">
<?php //  echo $this->Navigation->quickExaminerSearching('quicksearch',2,__('Examiner name', true),true); ?>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div class="current_content">
<dl>
<?php echo __('Device name',true);?> :
<?php echo $device['Device']['name'];?>
</dl>
<dl>
<?php echo __('Registration no.',true);?> :
<?php echo $device['Device']['registration_no'];?>
</dl>
<dl>
<?php echo __('Working place',true);?> :
<?php echo $device['Device']['working_place'];?>
</dl>
<dl>
<?php
echo __('Registered in the following testing methods',true) . '<br>';
if(count($device['Testingmethods']) > 0){
	foreach($device['Testingmethods'] as $_key => $_data){
		if($_key > 0)echo '; ';
		echo $_data['verfahren'] ;
	}
}
?>
</dl>
</div>
</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
