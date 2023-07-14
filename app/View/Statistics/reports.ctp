	<?php
	$i = 0;
	foreach ($reportnumbers as $reportnumber):
		
		if($reportnumber['Reportnumber']['print'] == 0) continue;
			
		$class = 'round';
		if($reportnumber['Reportnumber']['status'] == 1){
			$class = ' closed';
		}
		if($reportnumber['Reportnumber']['status'] == 2){
			$class .= ' closed2';
		}
		if($reportnumber['Reportnumber']['status'] == 3){
			$class .= ' settled';
		}
		if($reportnumber['Reportnumber']['delete'] == 1){
			$class .= ' delete';
		}

		echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber, 2)),array(
					'controller' => 'reportnumbers',
					'action' => 'pdf',
					$reportnumber['Reportnumber']['topproject_id'],
					$reportnumber['Reportnumber']['equipment_type_id'],
					$reportnumber['Reportnumber']['equipment_id'],
					$reportnumber['Reportnumber']['order_id'],
					$reportnumber['Reportnumber']['report_id'],
					$reportnumber['Reportnumber']['id']
				),
				array(
					'title' => __('print report',true),
					'target' => '_blank',
					'class' => $class,
				)
			);
		
		?>
		<?php
/*
		$this->request->projectvars['VarsArray'][5] = $reportnumber['Reportnumber']['id'];
		$separatpr = Configure::read('Separator');

		if($reportnumber['Reportnumber']['delete'] != 1){
		
		
		$class = 'round ajax hasmenu';
		$action = 'edit';
				
		echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber, 2)),array(
					'action' => $action,
					$reportnumber['Reportnumber']['topproject_id'],
					$reportnumber['Reportnumber']['equipment_type_id'],
					$reportnumber['Reportnumber']['equipment_id'],
					$reportnumber['Reportnumber']['order_id'],
					$reportnumber['Reportnumber']['report_id'],
//					$reportnumber['Reportnumber']['testingmethod_id'],
					$reportnumber['Reportnumber']['id']
				),
				array(
					'class' => $class,
					'rev' =>
						$reportnumber['Reportnumber']['topproject_id'] . '/' .
						$reportnumber['Reportnumber']['equipment_type_id'] . '/' .
						$reportnumber['Reportnumber']['equipment_id'] . '/' .
						$reportnumber['Reportnumber']['order_id'] . '/' .
						$reportnumber['Reportnumber']['report_id'] . '/' .
//						$reportnumber['Reportnumber']['testingmethod_id'] . '/' .
						$reportnumber['Reportnumber']['id']
				)
			);

		}
		elseif($reportnumber['Reportnumber']['delete'] == 1){
			echo h($reportnumber['Reportnumber']['year']).'-'.h($reportnumber['Reportnumber']['number']);

//			echo $this->Navigation->makeLink('reportnumbers','history',__('History'),'round modal',null,$this->request->projectvars['VarsArray']);
			$roll = $authUser->user('Roll');

			if($roll['id']<=1) {
//				echo $this->Navigation->makeLink('reportnumbers','pdf',__('Print'),'icon icon_labelprint print',null,array($this->request->projectvars['VarsArray'][4],1));
			}
		}

		?>
        </td>
        <td>
        <?php
		echo $this->ViewData->ShowInfo($reportnumber);
		?>
        </td>        
		<td>
		<?php
		if($reportnumber['Reportnumber']['delete'] != 1){
			$colors = array();
			$title = array();
			$title[] = __('Testing areas'); 
			if(Configure::read('WeldManager.color')) {
				if(isset($reportnumber['weld_types'][0]) && $reportnumber['weld_types'][0] == 1){
					$colors[] = 'unevaled_welds'; 
					$title[] = __('Unevaled welds',true); 
				}
				if(isset($reportnumber['weld_types'][1]) && $reportnumber['weld_types'][1] == 1){
					$colors[] = 'evaled_welds'; 
					$title[] = __('Evaled welds',true); 
				}
				if(isset($reportnumber['weld_types'][2]) && $reportnumber['weld_types'][2] == 1){
					$colors[] = 'declined_welds'; 
					$title[] = __('Defect welds',true); 
				}
			}

			if(Configure::read('WeldManager.show')) {
				if(!isset($xml[$reportnumber['Testingmethod']['value']])) { CakeLog::write('missing-testingmethod', print_r($reportnumber['Reportnumber']['id'], true)); }
				else { 
					if(!Configure::read('WeldManager.hideUseless') || 0 != count($xml[$reportnumber['Testingmethod']['value']]['settings']->xpath('Report'.ucfirst($reportnumber['Testingmethod']['value']).'Evaluation/*[key="id"]'))) {
						echo $this->Navigation->makeLink('reportnumbers','testingAreas',$title,'round_white modal'.(!empty($colors) ? ' '.join(' ',$colors) : null),null,$this->request->projectvars['VarsArray']);
					}
				}
			}
		} else {
			echo $this->Navigation->makeLink('reportnumbers','history',__('History'),'round modal',null,$this->request->projectvars['VarsArray']);
		}
*/		
		?>
<?php endforeach; ?>

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

<script type="text/javascript">
	$(document).ready(function(){

		$data = {
			"ajax_true": 1,
			"extra": {
				"width": Math.max(600, 1*$('#datatable').width()),
				"height": Math.min(800, Math.max(500, 1*$('#datatable').height()))
			}
		}

	$("div.paging a").click(function(){
		$("#reports").load($(this).attr("href"), {"type": "reports"})
		return false;
	});

	});
</script>

    