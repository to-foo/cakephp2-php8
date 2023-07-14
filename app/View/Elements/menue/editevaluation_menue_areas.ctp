<?php
if(!isset($this->request->data['paginationOverview']['real_struktur'])) return;
if(count($this->request->data['paginationOverview']['real_struktur']) == 0) return;

if(isset($this->request->data['paginationOverview']['has_no_position'])) $no_position = 1;

foreach ($this->request->data['paginationOverview']['real_struktur'] as $key => $value) {

	$ClassD = NULL;
	$ClassD .= 'icon icon_small icon_weld json ';

	if(!isset($this->request->data['paginationOverview']['results']['discription'])) continue;

	if(!isset($no_position)){

		if(isset($this->request->data['paginationOverview']['real_struktur'][$key][$this->request->data['paginationOverview']['current_id']])){
			$ClassD .= 'small_active ';
		} else {
			$ClassD .= 'small_passiv ';
		}
	}

	if($this->request->data['paginationOverview']['results']['discription'][$key] == 0) $ClassD .= 'small_no_result ';
	if($this->request->data['paginationOverview']['results']['discription'][$key] == 1) $ClassD .= 'small_okay_result ';
	if($this->request->data['paginationOverview']['results']['discription'][$key] == 2) $ClassD .= 'small_error_result ';

	if(isset($no_position) && $value == $this->request->data['paginationOverview']['current_id']){

		$ClassD .= 'small_active ';

	} else {
		$ClassD .= 'small_passiv ';

	}

	if(isset($this->request->data['paginationOverview']['real_struktur'][$key][$this->request->data['paginationOverview']['current_id']]) && $this->request->data['paginationOverview']['current_weld_edit_status'] == 1){
		$Rev = 1;
	} else {
		$Rev = 0;
	}

	if($this->request->data['paginationOverview']['current_weld_edit_status'] != 1 && !isset($this->request->data['paginationOverview']['current_weld'][$key])) $ClassD .= 'icon_hidden ';

	echo $this->Html->Link($key,array(
		'action' => 'editevalution',
		$this->request->projectvars['projectID'],
		$this->request->projectvars['cascadeID'],
		$this->request->projectvars['orderID'],
		$this->request->projectvars['reportID'],
		$this->request->projectvars['reportnumberID'],
		$this->request->data['paginationOverview']['weld_struktur_id'][$key],
		1
		),
		array(
			'rel' => $this->request->data['paginationOverview']['weld_struktur_id'][$key],
			'rev' => $Rev,
			'data-desc' => $key,
			'class' => $ClassD,
			'title' => $this->request->data['paginationOverview']['weld_tooltip'][$key]
			)
		);

		if(!is_array($value)) $HidePosition = 1;
		elseif(count($value) == 1) $HidePosition = 1;
		else $HidePosition = 0;

		if(!is_array($value)) continue;

		foreach ($value as $_key => $_value) {

			if(!isset($this->request->data['paginationOverview']['results']['position'][$key][$_key])) continue;

			$ClassP = NULL;
			$ClassP .= 'icon icon_small icon_exam_part json ';

			if($this->request->data['paginationOverview']['results']['position'][$key][$_key] == 0) $ClassP .= 'small_no_result ';
			if($this->request->data['paginationOverview']['results']['position'][$key][$_key] == 1) $ClassP .= 'small_okay_result ';
			if($this->request->data['paginationOverview']['results']['position'][$key][$_key] == 2) $ClassP .= 'small_error_result ';

			$ActivePassive = 'small_passiv ';

			if($this->request->data['paginationOverview']['current_id'] == $_key) $ActivePassive = 'small_active ';

			$ClassP .= $ActivePassive;

			if($this->request->data['paginationOverview']['current_weld_edit_status'] == 1) $ClassP .= 'icon_hidden ';
			if($this->request->data['paginationOverview']['current_weld_edit_status'] != 1 && !isset($this->request->data['paginationOverview']['current_weld'][$key])) $ClassP .= 'icon_hidden ';

			if($HidePosition == 1 && strpos($ClassP,'icon_hidden') == false) $ClassP .= 'icon_hidden ';

			if(isset($this->request->data['paginationOverview']['position_tooltip'][$_key])){

				echo $this->Html->Link($_value,array('action' => 'editevalution',
					$this->request->projectvars['projectID'],
					$this->request->projectvars['cascadeID'],
					$this->request->projectvars['orderID'],
					$this->request->projectvars['reportID'],
					$this->request->projectvars['reportnumberID'],
					$_key,
					0
					),
					array(
						'rel' => $_key,
						'data-desc' => $key,
						'class' => $ClassP,
						'title' => $this->request->data['paginationOverview']['position_tooltip'][$_key]
					)
				);

			}
		}
}
?>
