<?php
$StatusArray[0] = 'deaktiv';
$StatusArray[1] = 'okay';
$StatusArray[2] = 'error';

echo '<div id="advance_tablecontent" class="advance_content">';
echo $this->element('advance/statistic_equipment');

echo '<table class="advancetool">';

if(isset($this->request->data['Scheme']['AdvancesData'])) $Test = Hash::extract($this->request->data['Scheme']['AdvancesData'], '{n}.AdvancesData.AdvancesDataDependency');

foreach ($this->request->data['Scheme']['AdvancesOrder']['AdvancesData'] as $_key => $_value) {

	if(!isset($_value['AdvancesData']['AdvancesDataDependency'])) continue;
	if(count($_value['AdvancesData']['AdvancesDataDependency']) == 0) continue;

	$Xml = $display_xml;

	if(isset($this->request->data['Xml']['MaxCount'])){

 		$xmlCount = $this->request->data['Xml']['MaxCount'];

		foreach ($this->request->data['Xml'] as $__key => $__value) {

			if(isset($__value['typs'][$_value['AdvancesData']['type']])){
				$Xml = $__value['xml']['settings'];
				break;
			}
		}
	}


	else $xmlCount = 0;

	foreach ($_value['AdvancesData']['AdvancesDataDependency'] as $__key => $__value) {

		if(count($__value) == 0) continue;

		echo '<tr class="subheadline">';
		echo '<td>' . __($__key,true) . '</td>';
		echo '<td>' . __('not started',true) . '</td>';
		echo '<td>' . __('completed',true) . '</td>';
		echo '<td>' . __('repair',true) . '</td>';

		$x = 0;

		foreach($Xml->section->children() as $____key => $____value){

			if(trim($____value->hidden) == 1) continue;
			if(empty($____value->model)) continue;
			if(empty($____value->key)) continue;

			++$x;

			echo '<td>' . trim($____value->description) . '</td>';

		}

		$EmptyFields = $xmlCount - $x;

		if($EmptyFields > 0){
			for ($i = 1; $i <= $EmptyFields; $i++) {
    		echo '<td></td>';
			}
		}

		echo '</tr>';

		foreach($__value as $___key => $___value){

			$Class = $StatusArray[$___value['AdvancesDataDependency']['value']];
			$ClassElements = array();

			$ClassElements[0] = 'icon';
			$ClassElements[1] = 'icon_delete';

			echo '<tr id="advance_detail_' . $___value['AdvancesDataDependency']['id'] . '" class="' . $Class . '">';
			echo '<td>';
			echo '<span title="' . __('Delete this advance point') . '" class="'. implode(' ',$ClassElements).'">' . $___value['AdvancesDataDependency']['id'] . '</span>';
			echo '</td>';

			$ClassElements[1] = 'edit_advance';
			$ClassElements[2] = 'empty';
			$ClassElements[3] = 'edit_empty';
			$ClassElements[4] = 'status_0';

			$status_class = 'deaktiv';
			if($this->request->data['Scheme']['AdvancesOrder']['Date']['status']['start'] == 0){
				$status_class = 'future';
			}
			if($this->request->data['Scheme']['AdvancesOrder']['Date']['status']['start'] == 1){
				$status_class = 'deaktiv';
			}
			if($this->request->data['Scheme']['AdvancesOrder']['Date']['status']['end'] == 1){
				$status_class = 'delay';
			}
			if($this->request->data['Scheme']['AdvancesOrder']['Date']['status']['end_delay'] == 1){
				$status_class = 'error';
			}

			if($___value['AdvancesDataDependency']['value'] == 0) $ClassElements[5] = $status_class;

			echo '<td class="progess_edit"><span class="'. implode(' ',$ClassElements).'">' . $___value['AdvancesDataDependency']['id'] . '</span></td>';

			unset($ClassElements[5]);

			$ClassElements[3] = 'edit_okay';
			$ClassElements[4] = 'status_1';
			if($___value['AdvancesDataDependency']['value'] == 1) $ClassElements[5] = $StatusArray[$___value['AdvancesDataDependency']['value']];

			echo '<td class="progess_edit"><span class="'. implode(' ',$ClassElements).'">' . $___value['AdvancesDataDependency']['id'] . '</span></td>';
			unset($ClassElements[5]);

			$ClassElements[3] = 'edit_error';
			$ClassElements[4] = 'status_2';
			if($___value['AdvancesDataDependency']['value'] == 2) $ClassElements[5] = $StatusArray[$___value['AdvancesDataDependency']['value']];
			echo '<td class="progess_edit"><span class="'. implode(' ',$ClassElements).'">' . $___value['AdvancesDataDependency']['id'] . '</span></td>';
			unset($ClassElements[5]);

			$x = 0;

			foreach($Xml->section->children() as $____key => $____value){

				if(trim($____value->hidden) == 1) continue;
				if(empty($____value->model)) continue;
				if(empty($____value->key)) continue;

				++$x;

				echo '<td><p class="editable" role="button" data-field="' . trim($____value->key) . '" data-id="' . $___value['AdvancesDataDependency']['id'] . '">';
				echo $___value['AdvancesDataDependency'][trim($____value->key)];
				echo '</p></td>';

			}

			$EmptyFields = $xmlCount - $x;

			if($EmptyFields > 0){
				for ($i = 1; $i <= $EmptyFields; $i++) {
	    		echo '<td></td>';
				}
			}

			echo '</tr>';
		}
	}
}
echo '</table>';
echo '</div>';
echo '</div>';
?>
