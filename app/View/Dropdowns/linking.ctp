<div class="modalarea">

	<h2><?php echo __('Please select the dropdown for transfering values to the chosen dropdown'); ?></h2>

	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('discription'); ?></th>
			<th><?php echo __('Field used in'); ?></th>
			<th></th>
			<th></th>
	</tr>
	<?php
	$i = 0;
	foreach ($modelDatas as $modelData):
		
		if($modelData['valueCount'] == 0) continue;
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr
	<?php echo $class;?>>
	<?php echo '<td>';?>
    <?php 
	echo h($modelData['Dropdown'][$select_lang]).(isset($modelData['valueCount']) && intval($modelData['valueCount']) > 0 ? ' ('.intval($modelData['valueCount']).')' : '');
	echo '<br>';
	echo __('gefunden in',true) . ' ' . $modelData['Report']['name'] . ', ';	
	echo __('erstellt von',true) . ' ' . $modelData['Testingcomp']['name'] . ' ';	

	?>
    <?php echo '</td>';?>
	<?php echo '<td>';?>
    <?php
	echo '<ul>';
	foreach($modelData['Dropdown']['available'] as $_available){
		echo '<li>'.$_available['verfahren'].' '.$_available['model'].'</li>';
	}
	echo '</ul>';
	echo '</td>';
	?>
	<?php
	echo '<td>';
	echo $this->Html->link(__('transfer the values to the chosen dropdown'), array('action' => 'linking',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$this->request->projectvars['VarsArray'][3],
				$this->request->projectvars['VarsArray'][4],
				$this->request->projectvars['VarsArray'][5],
				$this->request->projectvars['VarsArray'][6],
				$this->request->projectvars['VarsArray'][7],
				$this->request->projectvars['VarsArray'][8],
				$this->request->projectvars['VarsArray'][9],
//				$this->request->projectvars['VarsArray'][10],
//				$this->request->projectvars['VarsArray'][11],
				$modelData['Dropdown']['id'])
		, array('class'=>'mymodal round'));
	echo '</td>';
	echo '<td>';
//	pr($modelData['Dropdown']['id']);
//	pr($modelData['Dropdown']['linking']);
	echo '</td>';
	?>

	</tr>
<?php endforeach; ?>

	</table>
	<div class="paging mymodal">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>
    </p>
</div>
<div class="clear" id="testdiv"></div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#dialog').scrollTop(0);
	});
</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>