<div class="modalarea">
	<h2><?php echo __('Order', true).' '.$order['Order']['auftrags_nr']; ?></h2>
<ul class="listemax">
<?php 
foreach ($testingmethods as $key => $testingmethod){
if(
$testingmethod['Testingmethod']['value'] != 'rt' &&
$testingmethod['Testingmethod']['value'] != 'mt' &&
$testingmethod['Testingmethod']['value'] != 'pt' 
) continue;
echo '<li>';
echo $this->Html->link(__('Invoices', true).' '.h($testingmethod['Testingmethod']['verfahren']).' ('.$StatistikArray[$key].')', 
			array(
				'controller' => 'invoices', 
				'action' => 'invoice',
				$this->request->projectvars['projectID'], 
				$this->request->projectvars['equipmentType'],
				$this->request->projectvars['equipment'],  
				$this->request->projectvars['orderID'],  
				$testingmethod['Testingmethod']['id'],
				2
				),
			array('class'=>'mymodal')
		); 
echo '</li>';
}
?>

</ul>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>