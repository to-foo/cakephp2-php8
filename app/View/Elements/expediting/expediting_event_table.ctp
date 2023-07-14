<div class="expediting_item">
<?php
echo '<h3>' . __('Progress schedule',true) . '</h3>';
echo $this->element('expediting/legende');
?>
<div class=" menue">
<?php
echo $this->Html->link(__('Delete Expediting',true),
	array_merge(
		array(
			'controller' => 'expeditings',
			'action' => 'deleteexpeditings'
		),
		$this->request->projectvars['VarsArray']
		),
	array(
		'class' => 'modal round',
		)
);

echo $this->Html->link(__('Add a expediting step',true),
	array_merge(
		array(
			'controller' => 'expeditings',
			'action' => 'addevent'
		),
		$this->request->projectvars['VarsArray']
	),
	array(
		'class' => 'modal round',
	)
);
?>	
</div>
<?php
if(!isset($this->request->data['Expediting'])) return;

echo '<table class="advancetool sortable">';
echo '<tr>';
echo '<th>' . __('Functions',true) . '</th>';
echo '<th>' . __('Email',true) . '</th>';
echo '<th>' . __('Reports',true) . '</th>';
echo '<th>' . __('Description',true) . '</th>';
echo '<th></th>';
echo '<th>' . __('Soll Date',true) . '</th>';
echo '<th>' . __('Is Date',true) . '</th>';
echo '<th></th>';
echo '<th class="large">' . __('Remark',true) . '</th>';
echo '</tr>';

foreach($this->request->data['Expediting'] as $_key => $_Expediting){

	$_Expediting['Expediting']['class'] .= ' ' . $_Expediting['Expediting']['hold_witness_point_class'];

	echo '<tr id="tr_sort_' . $_Expediting['Expediting']['id'] . '" rev="' . $_Expediting['Expediting']['id'] . '" rel="' . $_Expediting['Expediting']['sequence'] . '" class="expediting_step expediting_step_' . $_Expediting['Expediting']['expediting_type_id'] .' ' . $_Expediting['Expediting']['class'] . '">';
	echo '<td>';

	echo $this->Html->link($_Expediting['Expediting']['description'],
			array(
				'controller' => 'expeditings',
				'action' => 'detail',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$_Expediting['Expediting']['expediting_type_id'],
				$_Expediting['Expediting']['id']
				),
		array(
			'title' => __('Expediting info',true),
			'class'=>'icon editlink modal'
		)
	);

	echo $this->Html->link($_Expediting['Expediting']['description'],
			array(
				'controller' => 'expeditings',
				'action' => 'delete',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$_Expediting['Expediting']['expediting_type_id'],
				$_Expediting['Expediting']['id']
				),
		array(
			'title' => __('Delete expetiting step',true),
			'class'=>'icon dellink modal'
		)
	);

	echo $this->Html->link($_Expediting['Expediting']['hold_witness_point_description'],
		array(
			'controller' => 'expeditings',
			'action' => 'expeditingevents',
			$this->request->projectvars['VarsArray'][0],
			$this->request->projectvars['VarsArray'][1],
			$this->request->projectvars['VarsArray'][2],
			$_Expediting['Expediting']['expediting_type_id'],
			$_Expediting['Expediting']['id']
		),
	  array(
	    'title' => __($_Expediting['Expediting']['hold_witness_point_description'],true) . ' - ' . __('Show history',true),
	    'class' =>array('modal icon show_history ' . $_Expediting['Expediting']['hold_witness_point_class'] . '  ' . $_Expediting['Expediting']['class']
	    )
	  )
	);

	echo '</td>';
	echo '<td>';

	if($this->request->data['Supplier']['stop_mail'] == 1){

		echo $this->Html->link(__('Email delivery for this step is deactive',true),
			'javascript:',
			array(
				'title' => __('Email delivery for this step is deactive',true),
				'class'=>'icon icon_stop_mail'
			)
		);

	} else {

		if($_Expediting['Expediting']['stop_mail'] == 0){

			echo $this->Html->link(__('Email delivery for this step is active',true),
			'javascript:',
			array(
				'title' => __('Email delivery for this step is active',true),
				'class'=>'icon icon_send_mail '
				)
			);

		} else {

			echo $this->Html->link(__('Email delivery for this step is deactive',true),
			'javascript:',
			array(
				'title' => __('Email delivery for this step is deactive',true),
				'class'=>'icon icon_stop_mail'
				)
			);
		}
		
	}

	echo '</td>';
	echo '<td>';
	echo $this->Html->link(__('Reports',true),
			array(
				'controller' => 'expeditings',
				'action' => 'showreports',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$_Expediting['Expediting']['id']
			),
		array(
			'title' => __('Show reports to this expetiting step',true),
			'class'=>'icon icon_view last_ten modal'
		)
	);

	//hier muss abgefragt werden ob schon Berichte vorhanden oder?
	echo $this->Html->link($_Expediting['Expediting']['description'],
			array(
				'controller' => 'testingmethods',
				'action' => 'listing',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->data['Supplier']['order_id'],
				1,
				0,
				0,
				$_Expediting['Expediting']['id']
			),
		array(
			'title' => __('Create report to this expetiting step',true),
			'class'=>'icon addlink modal'
		)
	);
	echo '</td>';
	echo '<td>';
	echo $_Expediting['Expediting']['description'];
	echo '</td>';
	echo '<td>';
	if($this->request->data['Supplier']['dynamic_termination'] == 1 && isset($_Expediting['Expediting']['period_datum'])){

		echo __($_Expediting['Expediting']['period_datum']) . ' ';
        echo $_Expediting['Expediting']['period_sign'] . $_Expediting['Expediting']['period_time'] . '&nbsp;';

		if($_Expediting['Expediting']['period_time'] == 1) $period_measure = Inflector::singularize($_Expediting['Expediting']['period_measure']);
		else $period_measure = Inflector::pluralize($_Expediting['Expediting']['period_measure']);

		echo __($period_measure);

	}
	echo '</td>';

	echo '</td>';

	echo '<td class="editsoll"><span class="edit editabledate editsoll" data-model="Expediting" data-field="date_soll" data-id="' . $_Expediting['Expediting']['id'] . '">' . $_Expediting['Expediting']['date_soll'] . '</span></td>';

	if($_Expediting['Expediting']['stop_for_next_step'] == 0){
		echo '<td class="editist"><span class="edit editabledate editist" data-model="Expediting" data-field="date_ist" data-id="' . $_Expediting['Expediting']['id'] . '">' . $_Expediting['Expediting']['date_ist'] . '</span></td>';
		echo '<td class="editist">';
		echo 	$this->Form->input(
						'expediting_type_date_checkbox_id_' . $_Expediting['Expediting']['id'],
							array(
								'type' => 'checkbox',
								'class' => 'expediting_date_checkbox',
								'label' => ' ',
								'value' => $_Expediting['Expediting']['id'],
								'checked' => ($_Expediting['Expediting']['finished'] == 1 ? 'checked' : null),
								'data-model' => 'Expediting',
								'data-field' => 'date_ist',
								'data-id' => $_Expediting['Expediting']['id'],
								'data-value' => $_Expediting['Expediting']['date_current'],
							)
						);
		echo '</td>';
	} else {
		echo '<td class="editist hide"><span class="edit editabledate editist" data-model="Expediting" data-field="date_ist" data-id="' . $_Expediting['Expediting']['id'] . '">' . $_Expediting['Expediting']['date_ist'] . '</span></td>';
		echo '<td class="editist hide">';
		echo 	$this->Form->input(
						'expediting_type_date_checkbox_id_' . $_Expediting['Expediting']['id'],
							array(
								'type' => 'checkbox',
								'class' => 'expediting_date_checkbox',
								'label' => ' ',
								'value' => $_Expediting['Expediting']['id'],
								'checked' => ($_Expediting['Expediting']['finished'] == 1 ? 'checked' : null),
								'data-model' => 'Expediting',
								'data-field' => 'date_ist',
								'data-id' => $_Expediting['Expediting']['id'],
								'data-value' => $_Expediting['Expediting']['date_current'],
							)
						);
		echo '</td>';
	}

	echo '<td class="edittext"><span class="edit editabletext edittext" data-model="Expediting" data-field="remark" data-id="' . $_Expediting['Expediting']['id'] . '">' . $_Expediting['Expediting']['remark'] . '</span></td>';
	echo '</tr>';

}

echo '</table>';
?>
</div>
