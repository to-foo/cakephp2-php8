<div class="expediting_item">
<?php
echo '<h3>' . __('Uploaded images',true) . '</h3>';

echo '<div class="hint">';

echo $this->Html->link(__('Image upload',true),
	array_merge(
		array('action' => 'images'),
		$this->request->projectvars['VarsArray']
		),
	array(
		'class' => 'modal round',
		)
	);

echo '</div>';

if(!isset($this->request->data['Supplierimage'])) return;
if(count($this->request->data['Supplierimage']) == 0) return;

$YesNowArray = array(0 => __('no',true),1 => __('yes',true));

echo '<div class="images advancetool"><ul>';

	foreach($this->request->data['Supplierimage'] as $key => $value){

		echo '<div class="subheadline expediting_step expediting_step_' . $key . '">' . $this->request->data['ExpeditingTypes'][$key] . '</div>';

		if(count($value) == 0){

			echo '<div class="empty expediting_step expediting_step_' . $key . '">' . __('No files uploaded',true) . '</div>';

			continue;

		}

		echo '<div class="flex">';

		foreach ($value as $_key => $_value) {

			if(($_value['file_exists']) == false) continue;

			echo '<li class="image expediting_step expediting_step_' . $key . '">';

			$VarsArray = $this->request->projectvars['VarsArray'];

			$VarsArray[3] = $_value['expediting_type_id'];
			$VarsArray[4] = $_value['expediting_id'];
			$VarsArray[5] = $_value['id'];

			echo $this->Html->link(' ',
				array_merge(
					array(
						'controller' => 'expeditings',
						'action' => 'edit'
					),
					$VarsArray
				),
				array(
					'class' => 'modal icon icon_edit',
					'escape' => false
				)
			);

			echo $this->Html->link(' ',
				array_merge(
					array('action' => 'image'),
					$VarsArray
				),
				array(
					'class' => 'image fancybox',
					'rev' => implode('/',$VarsArray),
					'rel' => $_value['id'],
					'style' => 'background-image:url('.$_value['imagedata'].')',
					'data-fancybox' => 'group',
					'data-caption' => $_value['description'],
					'escape' => false
					)
			);

			echo '</li>';

			unset($VarsArray);

		}

		echo '</div>';
	}

echo '</div></ul>';
?>
</div>
