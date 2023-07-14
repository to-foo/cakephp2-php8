<?php
if(!isset($this->request->data['Scheme'])){

	echo '<div class="hint">';
	echo '<p>' . __('No advances available.') . '</p>';
	echo '</div>';
	echo '</div>';
	return;

}

echo '<div class="advance_diagrammcontent_landingpage">';
echo $this->element('advance/advance_cascadegroup_schedule');
echo '</div>';
?>
