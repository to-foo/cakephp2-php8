<?php if(!isset($this->request->data['Suppliers'])) return;?>

<table id="" class="advancetool">

<?php // echo $this->element('expediting/expediting_table_head');?>

<?php

if(!isset($paging)) $paging['tr_marker'] = null;

$i = 0;

foreach($this->request->data['Suppliers'] as $key => $value){

	echo '<tr>';
	echo '<td>';

	echo $this->Html->link($value['Supplier']['unit'] . ' - ' . $value['Supplier']['equipment'] . ' (' . $value['Supplier']['equipment_name'] . ')',
				array_merge(
					array('controller'=>'suppliers','action' => 'overview'),
					array($this->request->projectvars['VarsArray'][0],$value['Supplier']['cascade_id'],$value['Supplier']['id'])
				),
				array(
					'title' => __('Goto',true) . ' ' . $value['Supplier']['unit'] . '-' . $value['Supplier']['equipment'],
					'class'=>'round  ajax'
				)
			);

	if($value['Supplier']['stop_mail'] == 1){

		echo '<br>';
		echo $this->Html->link(__('Email delivery for this step is deactive',true),
			'javascript:',
			array(
				'title' => __('Email delivery for this step is deactive',true),
				'class'=>'icon icon_stop_mail '
			)
		);

	} else {

		echo '<br>';
		echo $this->Html->link(__('Email delivery for this step is active',true),
		'javascript:',
			array(
				'title' => __('Email delivery for this step is active',true),
				'class'=>'icon icon_send_mail '
			)
		);

	}

	echo '</td>';
	echo '<td class="">';
	echo $this->element('expediting/supplier_table_infos',array('value' => $value));
	echo '</td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td colspan="100%">';
	echo $this->element('expediting/expediting_table_infos',array('value' => $value));
	echo '</td>';
	echo '</tr>';


}

?>

</table>
