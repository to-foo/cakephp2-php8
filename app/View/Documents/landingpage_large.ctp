<h3><?php echo __('Documents',true); ?></h3>
<div class="quicksearch">
<?php
echo $this->Html->link(__('Open documents modul',true),
	array(
		'controller' => 'documents',
		'action' => 'index'
	),
	array(
		'class' => 'round ajax',
		'title' => __('Open documents modul',true)
	)
);

if(isset($ControllerQuickSearch)){
	echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,false);
}
?>
</div>
<ul class="listemax">
<?php
foreach($documents_testingmethods as $_key => $_documents_testingmethods){
	echo '<li>';
	$this->request->projectvars['VarsArray'][15] = $_documents_testingmethods['DocumentTestingmethod']['id'];
	echo $this->Html->link($_documents_testingmethods['DocumentTestingmethod']['verfahren'],array_merge(array('action' => 'overview'),$this->request->projectvars['VarsArray']),array('class' => 'ajax'));
	echo '</li>';
}
?>
</ul>
<?php echo $this->element('js/ajax_modal_link');?>
<?php echo $this->element('js/ajax_link');?>
