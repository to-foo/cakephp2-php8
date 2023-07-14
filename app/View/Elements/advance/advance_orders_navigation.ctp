<?php
$SettingsLInk = $this->request->projectvars['VarsArray'];
$SettingsLInk[1] = $StartId;

$OrderLink = $this->request->projectvars['VarsArray'];
$OrderLink[1] = $this->request->data['Scheme']['AdvancesOrder']['AdvancesOrder']['cascade_id'];
$OrderLink[2] = $this->request->data['Scheme']['AdvancesOrder']['AdvancesOrder']['order_id'];

echo '<div class="hint">';


	if(isset($this->request->data['BreadcrumpArray'])) echo $this->element('advance/bread_crump');

	echo '<div class="advance_navi">';

	echo $this->element('navigation/change_modul_progress');
/*
	if(isset($this->request->data['Cascade'])){
		echo $this->Html->link(__('Back'),array_merge(
			array(
				'action' => 'json_scheme'
			),
			$SettingsLInk),
			array(
				'rev' => $this->request->data['Cascade']['parent'],
				'rel' => 'advance_area',
				'title' => __('Back'),
				'class' => 'round blank'
			)
		);
	}
*/
	if(isset($StartId)){

		echo $this->Form->input('SelectMenue',array(
			'label' => false,
			'div' => false,
			'type' => 'select',
			'selected' => $StartId,
			'options' => $this->request->data['Menue']
			)
		);
	}

	if(isset($this->request->data['Equipmentmenue'])){

		echo $this->Form->input('SelectEquipment',array(
			'label' => false,
			'div' => false,
			'type' => 'select',
			'selected' => $StartId,
			'options' => $this->request->data['Equipmentmenue']
			)
		);
	}

	echo $this->Html->link(__('Advance settings'),array_merge(
		array(
			'action' => 'advance_settings'
			),
			$SettingsLInk
		),
		array(
			'title' => __('Add Advance Data'),
			'class' => 'round modal'
			)
		);

	echo $this->Html->link(__('Add new advance points'),
		array_merge(
			array(
				'action' => 'advance_add'
			),
			$OrderLink
		),
		array(
			'title' => __('Add new advance points'),
			'rel' => $this->request->data['Scheme']['AdvancesOrder']['AdvancesOrder']['order_id'],
			'class' => 'round add_advance_point'
			)
		);

		echo $this->Html->link(__('Print checklist'),
			array_merge(
				array(
					'action' => 'checklist'
				),
				$OrderLink
			),
			array(
				'title' => __('Print checklist'),
				'class' => 'round',
				'targed' => '_blank'
				)
			);
/*
			echo $this->Html->link(__('Print statistic'),
				array_merge(
					array(
						'action' => 'printstatistic'
					),
					$OrderLink
				),
				array(
					'title' => __('Print checklist'),
					'class' => 'round',
					'targed' => '_blank'
					)
				);
*/
  echo '</div>';

	echo $this->element('advance/progress_legende');

echo '</div>';
?>
