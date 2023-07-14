<?php
if(!isset($LinkArray)) return;
if(count($LinkArray) == 0) return;

echo '<div class="hint">';

foreach ($LinkArray as $key => $value) {

	if(!isset($value['discription'])) continue;
	if(!isset($value['controller'])) continue;
	if(!isset($value['action'])) continue;
	if(!isset($value['title'])) continue;
	if(!isset($value['class'])) continue;

	echo $this->Html->link($value['discription'],
		array_merge(
			array('controller' => $value['controller']),
			array('action' => $value['action']),
			$value['terms']
		),
		array(
			'title' => $value['title'],
			'class' => $value['class']
		)
	);
}

echo '</div>';
?>
