<?php
echo '<fieldset class="multiple_field">';
echo $this->Form->input('ExaminerTestingcomps.widget', array(
      'label' => __('Assign Testingcomps',true),
      'multiple' => true,
      'required' => true,
      'options' => $testingcompSelection ?? '',
      'selected' => $testingcompAssigned ?? '',
));

echo '</fieldset>';
?>
