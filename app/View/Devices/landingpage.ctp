<div class="">
<h4><?php echo __('Devices dates and tasks',true); ?></h4>
<ul class="listemax">
<?php
foreach($devices_testingmethods as $_key => $_devices_testingmethods){
	echo '<li>';
	$this->request->projectvars['VarsArray'][15] = $_devices_testingmethods['DeviceTestingmethod']['id'];
	echo $this->Html->link($_devices_testingmethods['DeviceTestingmethod']['verfahren'],array_merge(array('action' => 'overview'),$this->request->projectvars['VarsArray']),array('class' => 'ajax'));
	echo '</li>';
}
?>
</ul>
</div>
<?php echo $this->element('js/ajax_link');?>
