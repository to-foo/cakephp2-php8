<div class="modalarea">
<h2><?php echo __('Documents',true); ?></h2>
<div class="quicksearch">
<?php // echo $this->element('barcode_scanner');?>
<?php
if(isset($ControllerQuickSearch)){
	echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,false);
}
echo $this->Html->link(__('Add document',true), array_merge(array('action' => 'add'), array()), array('class' => 'modal icon icon_documents_add','title' => __('Add document',true)));
?>
</div>
<?php echo $this->element('Flash/_messages');?>
<div id="container_summary" class="container_summary"></div>

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
</div>
<?php echo $this->element('js/ajax_modal_link'); ?>
