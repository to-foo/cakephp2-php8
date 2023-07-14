<div class="modalarea">
<h2><?php echo __('Reports') . ' ' . __('Order') . ' ' . $results[0]['Order']['auftrags_nr'];?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('number'); ?></th>
			<th><?php echo $this->Paginator->sort('Topproject'); ?></th>
			<th><?php echo $this->Paginator->sort('EquipmentType'); ?></th>
			<th><?php echo $this->Paginator->sort('Equipment'); ?></th>
			<th><?php echo $this->Paginator->sort('auftrags_nr'); ?></th>
			<th><?php echo $this->Paginator->sort('Reportnumber.modified'); ?></th>
			<th><?php echo __('Messpunkte') ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th class="actions">&nbsp;</th>
	</tr>
	<?php 
	$i = 0;

	foreach ($results as $reportnumber):

		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' altrow';
		}
		if($reportnumber['Reportnumber']['status'] == 1){
			$class = ' closed';
		}
		if($reportnumber['Reportnumber']['status'] == 2){
			$class = ' settled';
		}
		if($reportnumber['Reportnumber']['delete'] == 1){
			$class = ' delete';
		}
	?>
	<tr class="<?php echo $class;?>">
		<td><?php echo h($reportnumber['Reportnumber']['year']).'-'.h($reportnumber['Reportnumber']['number']).'-'.h($reportnumber['Testingmethod']['name']);?></td>
		<td><?php echo h($reportnumber['Topproject']['projektname']);?></td>
		<td><?php echo h($reportnumber['EquipmentType']['discription']);?></td>
		<td><?php echo h($reportnumber['Equipment']['discription']);?></td>
		<td><?php echo h($reportnumber['Order']['auftrags_nr']);?></td>
		<td><?php echo h($reportnumber['Reportnumber']['modified']);?></td>
		<td>
		<?php 
			$x = 0;
			if(isset($reportnumber['ReportEvaluation']) && count($reportnumber['ReportEvaluation']) > 0){
				foreach($reportnumber['ReportEvaluation'] as $_ReportEvaluation){
					foreach($_ReportEvaluation as $__key => $__ReportEvaluation){
						
						if($x > 0){
							echo '; ';
						}
					
						echo $__ReportEvaluation['description'];
						$x++;
					}
				}
			}
		?>
        </td>
		<td><?php echo $this->ViewData->ShowStatus($reportnumber); ?></td>
		<td class="actions">
			<?php
		$this->request->projectvars['VarsArray'][0] = $reportnumber['Reportnumber']['topproject_id'];
		$this->request->projectvars['VarsArray'][1] = $reportnumber['Reportnumber']['cascade_id'];
		$this->request->projectvars['VarsArray'][2] = $reportnumber['Reportnumber']['order_id'];
		$this->request->projectvars['VarsArray'][3] = $reportnumber['Reportnumber']['report_id'];
		$this->request->projectvars['VarsArray'][4] = $reportnumber['Reportnumber']['id'];
		if($reportnumber['Reportnumber']['delete'] != 1){						 
				echo $this->Navigation->makeLink('reportnumbers','delete',__('Delete'),'icon icon_delete ajax','text_delete_report',$this->request->projectvars['VarsArray']);
				echo $this->Navigation->makeLink('reportnumbers','edit',__('Edit'),'icon icon_edit ajax',null,$this->request->projectvars['VarsArray']);
				echo $this->Navigation->makeLink('reportnumbers','view',__('View'),'icon icon_view ajax',null,$this->request->projectvars['VarsArray']);
			}
		elseif($reportnumber['Reportnumber']['delete'] == 1){
				echo $this->Navigation->makeLink('reportnumbers','history',__('History'),'round mymodal',null,$this->request->projectvars['VarsArray']);
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
   	<div class="paging">
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
<?php echo $this->JqueryScripte->ModalFunctions(); ?>

