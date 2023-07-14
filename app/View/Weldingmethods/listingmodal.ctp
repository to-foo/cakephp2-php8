<div class="modalarea">
	<h2><?php echo __('Create report').' '.$reportName; ?></h2>
<ul class="listemax">
<?php foreach ($testingmethods as $testingmethod): ?>
<li class="icon_discription icon_add"><span></span>
<?php
	$data = array('class' => 'ajax');
	if(isset($this->request->data['linked']) && $this->request->data['linked'] == 1) $data['class'] .= ' assignlink';
	
	$this->request->projectvars['VarsArray'][5] = $testingmethod['Testingmethod']['id'];
	echo $this->Html->link(h($testingmethod['Testingmethod']['verfahren']), 
		array_merge(array('controller' => 'reportnumbers','action' => 'add'),$this->request->projectvars['VarsArray']),
		$data
		); 
?></li>
<?php endforeach; ?>
</ul>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>