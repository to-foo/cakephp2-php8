<?php
if(count($summary) == 0) return false;

echo '<ul class="listemax">';

foreach ($summary as $key => $value) {

	$desc  = $value['examiner']['name'] . ' ' . $value['examiner']['first_name'] . ' (';
	if(isset($value['certificate']['testingmethod'])) 	$desc .= $value['certificate']['testingmethod'] . ' ';
	$desc .= $value['certificate']['certificat'] . ')';

	echo '<li class="' . $class . '">';

	echo $this->Html->link($desc,
		array_merge(
			array(
				'controller' => 'examiners',
				'action' => 'overview'
			),
			array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,$value['certificate']['examiner_id'])
		),
		array(
			'class' => 'ajax'
		)
	);

	echo ($value[0]);

	echo '</li>';

}

echo '</ul>';

?>
