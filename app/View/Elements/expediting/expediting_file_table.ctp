<div class="expediting_item">
<?php
echo '<h3>' . __('Uploaded files',true) . '</h3>';

$YesNowArray = array(0 => __('no',true),1 => __('yes',true));

echo '<div class="hint">';

echo $this->Html->link(__('File upload',true),
	array_merge(
		array('action' => 'files'),
		$this->request->projectvars['VarsArray']
		),
	array(
		'class' => 'modal round',
		)
	);

echo '</div>';

if(!isset($this->request->data['Supplierfile'])) return;
if(count($this->request->data['Supplierfile']) == 0) return;

echo '<table class="advancetool">';

echo '<tr>';
echo '<th> </th>';
echo '<th>' . __('Description',true) . '</th>';
echo '<th class="large">' . __('Remark',true) . '</th>';
echo '</tr>';
foreach($this->request->data['Supplierfile'] as $key => $value){

	$VarsArray = $this->request->projectvars['VarsArray'];
	$VarsArray[3] = $key;

	echo '<tr class="expediting_step subheadline expediting_step_' . $key . '">';
	echo '<td>';

	echo 	$this->Form->input(
					'expediting_type_file_checkbox_id_' . $key,
						array(
							'type' => 'checkbox',
							'class' => 'expediting_type_checkbox',
							'label' => ' ',
							'value' => $key,
						)
					);

	echo '<br>';

	if(count($value) > 0){

		echo $this->Html->link($key,
		array_merge(
		array('action' => 'getmanyfiles'),
		$VarsArray
		),
		array(
		'class' => 'massaction_download icon icon_download_okay',
		'title' => __('Download',true),
		'rel' => 'files',
		'rev' => $key,
		)
		);

		echo $this->Html->link($key,
		array_merge(
		array('controller' => 'expeditings'),
		array('action' => 'sendmanyfiles'),
		$VarsArray
		),
		array(
		'class' => 'massaction_mail icon icon_on_mail',
		'title' => __('Send',true),
		'rel' => 'files',
		'rev' => $key,
		)
		);
	}

	echo '<br>';

	echo $this->Html->link($key,
	array_merge(
	array('controller' => 'suppliers'),
	array('action' => 'files'),
	$VarsArray
	),
	array(
	'class' => 'massaction_upload icon icon_upload',
	'title' => __('Upload',true),
	'rel' => 'files',
	'rev' => $key,
	)
	);

	if(count($value) > 0){
		echo $this->Html->link($key,
		array_merge(
		array('controller' => 'expeditings'),
		array('action' => 'delmanyfiles'),
		$VarsArray
		),
		array(
		'class' => 'icon dellink massaction_mail',
		'title' => __('Delete files',true),
		'rel' => 'files',
		'rev' => $key,
		)
		);
	}

	echo '</td>';
	echo '<td colspan="2">';
	echo $this->request->data['ExpeditingTypes'][$key];
	echo '</td>';
	echo '</tr>';

	if(count($value) == 0){

		echo '<tr class="expediting_step expediting_step_' . $key . '">';
		echo '<td colspan="3">' . __('No files uploaded',true) . '</td>';
		echo '</tr>';
		continue;

	}

	foreach ($value as $_key => $_value) {
		$VarsArray = $this->request->projectvars['VarsArray'];
		$VarsArray[3] = $_value['expediting_type_id'];
		$VarsArray[4] = $_value['expediting_id'];
		$VarsArray[5] = $_value['id'];

		echo '<tr class="expediting_step expediting_step_' . $key . '" data-id="' . $_value['id'] .'">';
		echo '<td class="input_content">';
		echo '<span class="nowrap">';

		echo 	$this->Form->input(
		        'expediting_file_checkbox_id_' . $_value['id'],
		          array(
		            'type' => 'checkbox',
		            'class' => 'expediting_file_checkbox',
		            'label' => ' ',
		            'value' => $_value['id'],
		          )
		        );

		echo '<div>';

		if($_value['file_exists'] == true){

			echo $this->Html->link($_value['basename'],
				array_merge(
					array('action' => 'getfile'),
					$VarsArray
					),
				array(
					'class' => 'filelink icon icon_download_okay',
					'title' => __('Download',true) . ' ' . $_value['basename']
					)
				);


			echo $this->Html->link($_value['basename'],
				array_merge(
					array('controller' => 'expeditings'),
					array('action' => 'sendmail'),
					$VarsArray
					),
				array(
					'class' => 'icon icon_on_mail modal',
					'title' => __('Send',true) . ' ' . $_value['basename']
					)
				);

			echo '<br>';

		} else {

		echo $this->Html->link($_value['basename'] . ' ' . __('file not found',true),
			'javascript:',
			array(
				'class' => 'icon icon_upload_red',
				'title' => $_value['basename'] . ' ' . __('file not found',true)
				)
			);
		}

		echo $this->Html->link($_value['basename'],
			'javascript:',
			array(
				'class' => 'icon icon_infos tooltip_content',
				'title' => __('Info',true) . ' ' . $_value['basename']
				)
			);


		echo '<span class="list_for_tooltip">';
		echo '<ul class="tooltip">';
		echo '<li>' . __('File name',true) . ': <b>' . $_value['basename'] . '</b></li>';
		echo '<li>' . __('Upload from',true) . ': <b>' . $_value['user_name'] . '</b></li>';
		echo '<li>' . __('Upload date',true) . ': <b>' . $_value['created'] . '</b></li>';
		echo '<li>' . __('Last update',true) . ': <b>' . $_value['modified'] . '</b></li>';

		if($_value['file_exists'] == 1) echo '<li class="success">' . __('File exists') . '</li>';
		else echo '<li class="error">' . __('File not found') . '</li>';

		echo '</ul>';
		echo '</span>';

		echo $this->Html->link($_value['basename'],
			array_merge(
				array('controller' => 'suppliers'),
				array('action' => 'delfile'),
				$VarsArray
				),
			array(
				'class' => 'icon dellink modal',
				'title' => __('Delete',true) . ' ' . $_value['basename']
				)
			);

		echo '</span>';
		echo '</div>';
		echo '</td>';

		echo '<td class="large tooltip" title="' . $_value['basename'] . '">';

		echo $this->Text->truncate($_value['basename'],
		    75,
		    array(
		        'ellipsis' => '...',
		        'exact' => true
		    )
		);

		'</td>';
		echo '<td><span class="edit editabletext" data-model="Supplierfile" data-field="remark" data-id="' . $_value['id'] . '">' . $_value['remark'] . '</span></td>';
		echo '</tr>';

		unset($VarsArray);

	}

	unset($VarsArray);

}
echo '</table>';
?>
</div>
