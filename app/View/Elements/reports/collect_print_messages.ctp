<?php
foreach ($PrintMessages as $key => $value) {

  if(count($value)== 0) continue;

  echo '<ul class="'.$key.'">';
  foreach ($value as $_key => $_value) echo '<li>' . $_value . '</li>';
  echo '</ul>';
}

echo $this->element('reports/printing_show_errors',array('errors' => $errors));

//if (isset($errors) && is_array($errors) && count($errors) > 0) $this->ViewData->showValidationErrors($errors);
?>
