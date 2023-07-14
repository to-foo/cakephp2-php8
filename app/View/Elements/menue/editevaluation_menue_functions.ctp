<?php
echo '<div id="SectionModeContainer" class="section_mode_container">' . $this->request->data['paginationOverview']['section_mode'] . '</div>';

if($this->request->projectvars['evalId'] == 0) $writeprotection = 'disabled';
//pr($this->request->data['paginationOverview']);
if(isset($this->request->data['paginationOverview']['current_weld'])) $Description = key($this->request->data['paginationOverview']['current_weld']);
else $Description = ' ';

if($hasPositionColum == true){

	$Position = $this->request->data['paginationOverview']['current_position'][$this->request->projectvars['evalId']];

	if(isset($this->request->data['paginationOverview']['weld_struktur_id'][$Description])) $this_first_weld_id = $this->request->data['paginationOverview']['weld_struktur_id'][$Description];
	else $this_first_weld_id = 0;

}

//pr($this->request->data['paginationOverview']);
if($this->request->projectvars['VarsArray'][6] == 1){
	$naht_dupli_desc = __('Duplicate',true);
	$naht_del_desc = __('Delete',true);
	}

if($this->request->projectvars['VarsArray'][6] == 0 && $hasPositionColum == true){

	if(empty($Position)) $Position = NULL;

	$naht_dupli_desc = __('Duplicate',true) . ' ' . $Description . '/' . $Position;
	$naht_del_desc = __('Delete',true)  . ' ' . $Description . '/' . $Position;

	}

$attribut_disabled = false;

if($reportnumber['Reportnumber']['status'] == 0) $attribut_disabled = true;
if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) $attribut_disabled = true;

if(!isset($naht_dupli_desc)) return;

//if($attribut_disabled === false) return;

if($attribut_disabled === true && isset($this->request->projectvars['evalId'])){

  if($reportnumber['Reportnumber']['repair_for'] == 0 || Configure::read('RepairAddNewEvaluation') == true){
    echo $this->Html->Link($naht_dupli_desc,
      array('action' => 'duplicatevalution',
        $this->request->projectvars['projectID'],
        $this->request->projectvars['cascadeID'],
        $this->request->projectvars['orderID'],
        $this->request->projectvars['reportID'],
        $this->request->projectvars['reportnumberID'],
        $this->request->projectvars['evalId'],
        $this->request->projectvars['VarsArray'][6]
      ),
      array(
        'class' => 'icon icon_dupli json',
        'title' => $naht_dupli_desc,
        'disabled'=> isset($writeprotection) && $writeprotection
      )
    );
  }

	if($reportnumber['Reportnumber']['repair_for'] == 0 || Configure::read('RepairAddNewEvaluation') == true) {
		echo $this->Html->Link($naht_del_desc,
			array('action' => 'deleteevalution',
				$this->request->projectvars['projectID'],
				$this->request->projectvars['cascadeID'],
				$this->request->projectvars['orderID'],
				$this->request->projectvars['reportID'],
				$this->request->projectvars['reportnumberID'],
				$this->request->projectvars['evalId'],
				$this->request->projectvars['VarsArray'][6]
			),
			array(
				'class' => 'icon icon_del json',
				'title' => $naht_del_desc,
				'disabled'=>isset($writeprotection) && $writeprotection
			)
		);
	}

	if(isset($ShowWeldLabel) && $ShowWeldLabel == true && isset($this->request->projectvars['evalId'])){
			echo $this->Html->Link(__('Print Label'),
				array('action' => 'printweldlabel',
					$this->request->projectvars['projectID'],
					$this->request->projectvars['cascadeID'],
					$this->request->projectvars['orderID'],
					$this->request->projectvars['reportID'],
					$this->request->projectvars['reportnumberID'],
					$this->request->projectvars['evalId'],
					$this->request->projectvars['VarsArray'][6]
				),
				array(
					'class' => 'icon icon_label showpdflink',
					'title' => __('Print Label')
				)
			);
	}

	if($reportnumber['Reportnumber']['repair_for'] == 0 || Configure::read('RepairAddNewEvaluation') == true){
		echo $this->Html->Link(__('new testing area'),
			array('action' => 'editevalution',
				$this->request->projectvars['projectID'],
				$this->request->projectvars['cascadeID'],
				$this->request->projectvars['orderID'],
				$this->request->projectvars['reportID'],
				$this->request->projectvars['reportnumberID'],
				0,
				0
			),
			array(
				'class' => 'icon icon_add json',
				'title' => __('new testing area'),
				'disabled'=>isset($writeprotection) && $writeprotection
			)
		);
	}

	if(Configure::read('DevelopmentsEnabled') == true && isset($this->request->projectvars['evalId'])){

		if(isset($reportnumber['development'])){

			if($reportnumber['development']['result'] == 0) $developmentClass = array('development_open',__('marked as not processed'));
			if($reportnumber['development']['result'] == 1) $developmentClass = array('development_rep',__('marked as error'));
			if($reportnumber['development']['result'] == 2) $developmentClass = array('development_ok',__('marked as processed'));

			echo $this->Html->link(__('Examination development',true),
				array('controller' => 'developments', 'action' => 'change',
					$reportnumber['Reportnumber']['topproject_id'],
					$reportnumber['Reportnumber']['cascade_id'],
					$reportnumber['Reportnumber']['order_id'],
					$reportnumber['Reportnumber']['report_id'],
					$reportnumber['Reportnumber']['id'],
					$this->request->projectvars['evalId'],
					'1'
				),
				array(
					'class'=>'this_development_evalution icon modal '.$developmentClass[0],
					'id'=>'this_development_'.$this->request->projectvars['evalId'],
					'title' => $developmentClass[1]
				)
			);
		}
	}

}

if($this->request->data['paginationOverview']['current_weld_edit_status'] == 1 && !isset($this->request->data['paginationOverview']['has_no_position'])){
	$WeldChangerSelected = $this->request->data['paginationOverview']['current_weld_description'];
} else {
	$WeldChangerSelected = $this->request->data['paginationOverview']['current_id'];
}

echo $this->Form->input('WeldChanger',
	array(
		'label' => false,
		'class' => 'WeldChanger',
		'options' => $this->request->data['paginationOverview']['dropdownmenue'],
		'selected' => $WeldChangerSelected,
 	)
);
?>
<div class="clear" style="height:0.5em"></div>
