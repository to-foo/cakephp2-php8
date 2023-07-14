<div class="">
<h2><?php echo __('Devices',true); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="quicksearch">
<?php
echo $this->element('barcode_scanner');
echo $this->element('searching/search_quick_device',array('target_id' => 'id','targedaction' => 'view','action' => 'quicksearch','minLength' => 2,'discription' => __('Intern number', true)));

echo $this->Html->link(__('Add device',true),
	array_merge(
		array('action' => 'add'),
		$this->request->projectvars['VarsArray']),
	array(
		'class' => 'modal icon icon_devices_add',
		'title' => __('Add device',true)
	)
);
?>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
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

<?php echo $this->element('js/ajax_modal_link');?>
<?php echo $this->element('js/ajax_breadcrumb_link');?>
<?php echo $this->element('js/ajax_link');?>
<?php echo $this->element('js/devices_bread_search_combined');?>
