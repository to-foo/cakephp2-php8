<?php
echo '<fieldset class="multiple_field">';

echo $this->Form->input('AssignedExaminers.widget', array(
      'label' => __('Selection Examiners',true),
      'multiple' => true,
      'options' => $assignableExaminers,
      'value' => $assignedExaminers,
));

echo '</fieldset>';
?>
