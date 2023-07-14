<div class="modalarea detail">
<h2><?php echo h(__('Weld repair tracking')); ?> <?php if(isset($reportnumber) && $reportnumber != null) echo $this->Pdf->ConstructReportNamePdf($reportnumber,3,true);?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(!isset($reportnumber)){
	echo '</div>';
	return;
}
?>
<?php
$Lang = $this->request->lang;

if($reportnumber['Repairs']['Statistic']['count'] ==  $reportnumber['Repairs']['Statistic']['status']['open']) {
echo '<div class="hint">';
echo $this->Html->Link(__('Create repair report for all mistake positions of this report',true),
		array(
			'controller' => 'reportnumbers',
			'action' => 'repair',
			$reportnumber['Reportnumber']['topproject_id'],
			$reportnumber['Reportnumber']['cascade_id'],
			$reportnumber['Reportnumber']['order_id'],
			$reportnumber['Reportnumber']['report_id'],
			$reportnumber['Reportnumber']['id'],
			0
			),
			array(
				'class' => 'round mymodal',
				'title' => __('Create repair report for all mistake positions of this report',true)
				)
			);
echo '</div>';
}
?>
<ul class="weld_mistakes">
<?php
$Model = $this->request->Model;
$Lang = $this->request->lang;
unset($reportnumber['Repairs']['Statistic']);

if(isset($arrayData['settings']->$Model->welder)) $WelderFild = 'welder';
if(isset($arrayData['settings']->$Model->welder_no)) $WelderFild = 'welder_no';

foreach($reportnumber['Repairs'] as $_key => $_data){

	echo '<li class="'.$_data['class'].' mistake">';
	echo '<span class="'.$_data['class'].'"></span>';

	echo '<dl>';
	echo '<dd>' . $this->Pdf->ConstructReportNamePdf($reportnumber,2,true) . '</dd>';
	echo '<dt>' . __('Report no.',true) . '</dt>';
	echo '</dl>';

	echo '<dl>';
	echo '<dd>' . $_data['mistake'][key($_data['mistake'])]['description'] . '</dd>';
	echo '<dt>' . trim($arrayData['settings']->$Model->description->discription->$Lang) . '</dt>';
	echo '</dl>';

	echo '<dl>';
	echo '<dd>' . $_data['mistake'][key($_data['mistake'])]['position'] . '</dd>';
	if(isset($arrayData['settings']->$Model->position->discription->$Lang)){
		echo '<dt>' . trim($arrayData['settings']->$Model->position->discription->$Lang) . '</dt>';
	}
	echo '</dl>';

	echo '<dl>';
	echo '<dd>' . $_data['mistake'][key($_data['mistake'])]['dimension'] . '</dd>';
	echo '<dt>' . trim($arrayData['settings']->$Model->dimension->discription->$Lang) . '</dt>';
	echo '</dl>';

	if(isset($WelderFild)){
		echo '<dl>';
		echo '<dd>' . $_data['mistake'][key($_data['mistake'])]['welder'] . '</dd>';
		echo '<dt>' . trim($arrayData['settings']->$Model->{$WelderFild}->discription->$Lang) . '</dt>';
		echo '</dl>';
	}

	echo '<dl>';
	if(isset($_data['mistake']['error'])) {
		echo '<dd>' . $_data['mistake'][key($_data['mistake'])]['error'] . '</dd>';
	}
	if(isset($arrayData['settings']->$Model->error->discription->$Lang)) {
		echo '<dt>' . trim($arrayData['settings']->$Model->error->discription->$Lang) . '</dt>';
	}
	echo '</dl>';

	echo '<dl>';
	echo '<dd><b>' . trim($arrayData['settings']->$Model->result->radiooption->value[$_data['mistake'][key($_data['mistake'])]['result']]) . '</b></dd>';
	echo '<dt>' . trim($arrayData['settings']->$Model->result->discription->$Lang) . '</dt>';
	echo '</dl>';

	echo '<dl>';
	echo '<dd>' . $_data['mistake'][key($_data['mistake'])]['modified'] . '</dd>';
	echo '<dt>' . __('modified',true) . '</dt>';
	echo '</dl>';

	echo '<div class="icons">';

	if(!isset($_data['history'])){
		echo $this->Html->Link(__('Create repair report of this mistake position',true),
									array(
										'controller' => 'reportnumbers',
										'action' => 'repair',
										$reportnumber['Reportnumber']['topproject_id'],
										$reportnumber['Reportnumber']['cascade_id'],
										$reportnumber['Reportnumber']['order_id'],
										$reportnumber['Reportnumber']['report_id'],
										$reportnumber['Reportnumber']['id'],
										$_data['mistake'][key($_data['mistake'])]['id']
									),
									array(
										'class' => 'icon icon_repair mymodal',
										'title' => __('Create repair report of this mistake position',true)
									)
								);

	}
	echo '</div>';

	if(!isset($_data['history'])){

		echo '</li>';
		continue;

	}


	echo '<ol class="history">';
	$x = 0;
	foreach($_data['history'] as $__key => $__data){
		$x++;

		$ReportDescription = $this->Pdf->ConstructReportNamePdf($__data,1,true);

		echo '<li class="repair">';
		echo '<span class="'.$__data[key($__data)]['class_for_repair_view'].'"></span>';
		echo '<dl>';
		echo '<dd>' . $ReportDescription . '</dd>';
		echo '<dt>' . __('Report no.',true) . '</dt>';
		echo '</dl>';

		echo '<dl>';
		echo '<dd>' . $__data[key($__data)]['description'] . '</dd>';
		echo '<dt>' . trim($arrayData['settings']->$Model->description->discription->$Lang) . '</dt>';
		echo '</dl>';

		if(isset($arrayData['settings']->$Model->position->discription->$Lang)){
			echo '<dl>';
			echo '<dd>' . $__data[key($__data)]['position'] . '</dd>';
			echo '<dt>' . trim($arrayData['settings']->$Model->position->discription->$Lang) . '</dt>';
			echo '</dl>';
		}


		if(isset($arrayData['settings']->$Model->welder->discription->$Lang)){
			echo '<dl>';
			echo '<dd>' . $__data[key($__data)]['welder'] . '</dd>';
			echo '<dt>' . trim($arrayData['settings']->$Model->welder->discription->$Lang) . '</dt>';
			echo '</dl>';
		}

		if(isset($__data[key($__data)]['error'])){
			echo '<dl>';
			echo '<dd>' . $__data[key($__data)]['error'] . '</dd>';
			echo '<dt>' . trim($arrayData['settings']->$Model->error->discription->$Lang) . '</dt>';
			echo '</dl>';
		}


		echo '<dl>';
		echo '<dd><b>' . trim($arrayData['settings']->$Model->result->radiooption->value[$__data[key($__data)]['result']]) . '</b></dd>';
		echo '<dt>' . trim($arrayData['settings']->$Model->result->discription->$Lang) . '</dt>';
		echo '</dl>';

		echo '<dl>';
		echo '<dd>' . $__data[key($__data)]['modified'] . '</dd>';
		echo '<dt>' . __('modified',true) . '</dt>';
		echo '</dl>';

		echo '<div class="message '.$__data[key($__data)]['class_for_repair_view'].'"><b>'.$x .'. ' . __('try',true) . '</b> - ' . $__data[key($__data)]['message_for_repair_view'].'</div>';
		echo '<div class="links"> ';
		if($__data[key($__data)]['class_for_repair_view'] == 'progress' && AuthComponent::user('testingcomp_id') == $__data['Reportnumber']['testingcomp_id']){
			echo $this->Html->Link(__('Show repair report',true),
									array(
										'controller' => 'reportnumbers',
										'action' => 'edit',
										$__data['Reportnumber']['topproject_id'],
										$__data['Reportnumber']['cascade_id'],
										$__data['Reportnumber']['order_id'],
										$__data['Reportnumber']['report_id'],
										$__data['Reportnumber']['id'],
										0
									),
									array(
										'class' => 'icon icon_repair ajax',
										'title' => __('Show repair report',true)
									)
								);
		}

		if($__data[key($__data)]['class_for_repair_view'] == 'success' || $__data[key($__data)]['class_for_repair_view'] == 'error'){

			if(AuthComponent::user('testingcomp_id') == $__data['Reportnumber']['testingcomp_id'] || AuthComponent::user('roll_id') < 5){
				echo $this->Html->Link(__('Print repair report',true),
									array(
										'controller' => 'reportnumbers',
										'action' => 'pdf',
										$__data['Reportnumber']['topproject_id'],
										$__data['Reportnumber']['cascade_id'],
										$__data['Reportnumber']['order_id'],
										$__data['Reportnumber']['report_id'],
										$__data['Reportnumber']['id'],
									),
									array(
										'class' => 'icon icon_print',
										'target' => '_blank',
										'title' => __('Print repair report',true)
									)
								);
				echo $this->Html->Link(__('Show repair report',true),
									array(
										'controller' => 'reportnumbers',
										'action' => 'view',
										$__data['Reportnumber']['topproject_id'],
										$__data['Reportnumber']['cascade_id'],
										$__data['Reportnumber']['order_id'],
										$__data['Reportnumber']['report_id'],
										$__data['Reportnumber']['id'],
										$__data[key($__data)]['id']
									),
									array(
										'class' => 'icon icon_view ajax',
										'title' => __('Show repair report',true)
									)
								);
			}
		}

		echo '</div>';
		echo '</li>';
	}

	echo '</ol>';
	echo '</li>';
}
?>
</ul>
</div>
