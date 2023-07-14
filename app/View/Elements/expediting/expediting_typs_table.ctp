<?php
if(!isset($this->request->data['ExpeditingType'])) return;

echo '<table class="advancetool">';
echo '<tr>';
echo '<th>' . __('Functions',true) . '</th>';
echo '<th>' . __('Description',true) . '</th>';
//echo '<th>' . __('Soll Date',true) . '</th>';
echo '<th class="large">' . __('Remark',true) . '</th>';
echo '</tr>';

foreach($this->request->data['ExpeditingType'] as $_key => $_Expediting) {
	echo '<tr id="expediting_type_id_' . $_Expediting['ExpeditingType']['id'] . '">';

	echo '<td>';

		echo $this->Html->link($_Expediting['ExpeditingType']['description'],
				array_merge(array(
					'controller' => 'expeditings',
					'action' => 'editexpediting',
				),$this->request->projectvars['VarsArray']),
			array(
				'title' => __('Delete expediting type',true),
				'class'=>'icon dellink json confirm_delete',
				'data-model' => 'ExpeditingType',
				'data-field' => 'deleted',
				'data-id' => $_Expediting['ExpeditingType']['id'],
				'data-value' => '1',
				'data-value-function-after' => 'expediting_type_id_' . $_Expediting['ExpeditingType']['id'],
				'data-confirm' => __('Will you delete this value'),
			)
		);		


	echo '</td>';

	echo '<td><span class="edit editabletext" data-model="ExpeditingType" data-field="description" data-id="' . $_Expediting['ExpeditingType']['id'] . '">' . $_Expediting['ExpeditingType']['description'] . '</span></td>';
	echo '<td><span class="edit editabletext" data-model="ExpeditingType" data-field="remark" data-id="' . $_Expediting['ExpeditingType']['id'] . '">' . $_Expediting['ExpeditingType']['remark'] . '</span></td>';

	echo '</tr>';


}

echo '</table>';
?>
</div>
