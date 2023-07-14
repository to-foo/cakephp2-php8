<?php
$StatusArray[0] = 'deaktiv';
$StatusArray[1] = 'okay';
$StatusArray[2] = 'error';

echo '<div id="advance_reportcontent" class="advance_content">';
echo '<table class="advancetool">';

if(isset($this->request->data['Scheme']['AdvancesData'])) $Test = Hash::extract($this->request->data['Scheme']['AdvancesData'], '{n}.AdvancesData.AdvancesDataDependency');


foreach($this->request->data['Scheme']['AdvancesOrder']['AdvancesData'] as $key => $value) {

	if(!isset($value['AdvancesDataReport'])) continue;
	if(count($value['AdvancesDataReport']) == 0) continue;

	foreach ($value['AdvancesDataReport'] as $_key => $_value) {

    if(!is_array($_value)) continue;
		if(count($_value) == 0) continue;

		echo '<tr class="subheadline">';
		echo '<td>' . __($_key,true) . '</td>';
		echo '<td>' . __('Status',true) . '</td>';
    echo '<td>' . __('Info',true) . '</td>';
		echo '<td>' . __('modified',true) . '</td>';
		echo '<td>' . __('created',true) . '</td>';
		echo '<td>' . __('User',true) . '</td>';
		echo '</tr>';

		foreach ($_value as $__key => $__value) {

			echo '<tr>';
			echo '<td>';

      if($__value['Reportnumber']['status'] == 0){

				echo $this->Html->link($__value['Reportnumber']['number'] . '-' . $__value['Reportnumber']['year'],array_merge(
  				array(
  					'controller' => 'reportnumbers',
  					'action' => 'edit'
  				),
  				$__value['PrintLink']),
  				array(
  					'title' => __('Edit'),
  					'class' => 'icon icon_edit ajax'
  				)
  			);

      } else {

        echo $this->Html->link($__value['Reportnumber']['number'] . '-' . $__value['Reportnumber']['year'],array_merge(
  				array(
  					'controller' => 'reportnumbers',
  					'action' => 'pdf'
  				),
  				$__value['PrintLink']),
  				array(
  					'title' => __('Print'),
  					'class' => 'icon icon_print showpdflink'
  				)
  			);

				echo $this->Html->link($__value['Reportnumber']['number'] . '-' . $__value['Reportnumber']['year'],array_merge(
  				array(
  					'controller' => 'reportnumbers',
  					'action' => 'edit'
  				),
  				$__value['PrintLink']),
  				array(
  					'title' => __('Edit'),
  					'class' => 'icon icon_edit ajax'
  				)
  			);

      }

			echo '</td>';
			echo '<td>' . $this->ViewData->ShowStatus($__value) . '</td>';
      echo '<td>' . $this->ViewData->ShowInfo($__value) . '</td>';
			echo '<td>' . $__value['Reportnumber']['modified'] . '</td>';
			echo '<td>' . $__value['Reportnumber']['created'] . '</td>';
			echo '<td>' . $__value['User']['name'] . '</td>';
			echo '</tr>';
		}
	}
}

echo '</table>';

echo '</div>';
?>
