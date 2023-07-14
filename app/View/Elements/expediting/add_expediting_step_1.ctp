<table id="" class="advancetool">

<?php

foreach($this->request->data['Cascade'] as $key => $value){

	$VarsArray = $this->request->projectvars['VarsArray'];

	echo '<tr>';

	echo '<td>';

	$Description = '';

	foreach ($value as $_key => $_value) {
		$Description .= $_value['Cascade']['discription'] . ' -> ';
	}

	$end = end($value);

	$VarsArray[0] = $end['Cascade']['topproject_id'];
	$VarsArray[1] = $end['Cascade']['id'];

	echo $Description;

	echo '</td>';
	echo '<td>';

	echo $this->Html->link(__('Add Expediting here',true),
	array_merge(
		array(
			'controller'=>'expeditings',
			'action' => 'addexpeditingobject'),
			$VarsArray
	),
	array(
		'title' => __('Add Expediting here',true),
		'class'=>'round addexpeditingobject',
	 )
	);

	echo '</td>';

	echo '</tr>';

}
?>

</table>
