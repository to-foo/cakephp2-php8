<?php
$this->request->projectvars['VarsArray'][6] = $_reportimages['Reportimage']['id'];

echo '<li class="image" data-sort="' . $_reportimages['Reportimage']['id'] . '">';

echo '<div class="image_navi">';

echo $this->Form->input('print_'.$_reportimages['Reportimage']['id'],
		array(
			'class' => 'checkbox print',
			'rev' => implode('/',$this->request->projectvars['VarsArray']),
			'rel'=>$_reportimages['Reportimage']['id'],
			'type'=>'checkbox',
			'value'=>1,
			'checked'=>(boolean)$_reportimages['Reportimage']['print'],
			'div' => 'input checkbox print',
			'disabled' => ($attribut_disabled === true) ? 'disabled' : ''
		)
);

echo $this->Html->link(__('Show'),
	array_merge(
		array('action' => 'image'),
		$this->request->projectvars['VarsArray']
	),
	array(
		'class' => 'icon fancybox show ',
//								'data-fancybox' => 'group',
		'data-caption' => $_reportimages['Reportimage']['discription'],
	)
);

echo $this->Html->link(__('Edit'),
	array_merge(
		array('action' => 'imagediscription'),
		$this->request->projectvars['VarsArray']
	),
	array(
		'class' => 'icon edit image_function ',
	)
);

echo $this->Html->link(__('Delete'),
	array_merge(
		array('action' => 'imagediscription'),
		$this->request->projectvars['VarsArray']
	),
	array(
		'class' => 'icon delete image_function ',
	)
);

echo '</div>';

echo '<span class="for_hasmenu1">';

if($_reportimages['Reportimage']['print'] == 0){
	$image_checkbox_title = __('Um dieses Bild in die PDF-Ausgabe aufzunehmen, aktivieren Sie bitte das Drucksymbol oben links im Bild.',true);
}
if($_reportimages['Reportimage']['print'] == 1){
	$image_checkbox_title = __('Um dieses Bild aus der PDF-Ausgabe zu entfernen, deaktivieren Sie bitte das Drucksymbol oben links im Bild.',true);
}

echo $this->Html->link('',
'javascript:',
array(
	'class' => '',
	'style' => 'background-image:url('.$_reportimages['Reportimage']['imagedata'].')',
	'title' => $image_checkbox_title,
	'escape' => false
	)
);

echo '</span>';

echo '</li>';

?>
