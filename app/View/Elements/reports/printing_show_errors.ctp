<?php
if(!is_array($errors)) return;
if(count($errors) == 0) return;

$Verfahren = $this->request->Verfahren;
$lang = $this->request->lang;

echo '<ul class="error">';

foreach ($errors as $key => $value) {

  foreach ($value as $_key => $_value) {

    foreach ($_value as $__key => $__value) {

      $Action = 'edit';
      if ($key == 'Evaluation') $Action = 'editevalution';
      if ($key == 'Sign') continue;

      echo '<li>';

      echo __($key, true) . ' -> ';

      if (isset($__value['position'])) echo $__value['position'] . ' -> ';

      echo $this->Html->link(
        trim($__value['description']->$lang),
        array_merge(
          array(
            'controller' => 'reportnumbers',
            'action' => $Action,
          ),
          $__value['reportnumber']
        ),
        array(
          'name' => 'error[Report'.$Verfahren.$key.']['.$_key.']',
          'title' => trim($__value['description']->$lang),
          'class' => 'error_fields',
          'rel' => $key,
          'rev' => 'Report'.$Verfahren.$key.Inflector::camelize($_key),
        )
      );

      echo ' -> ';
      echo trim($__value['message']);

      echo '</li>';
    }
  }
}

echo '</ul>';
if (isset($errors['Sign'])&& !empty($errors['Sign'])) {
  echo '<h4>'; echo __('Sign'); echo '</h4>';
  echo '<ul class="error">';
  foreach ($errors['Sign'] as $ekey => $evalue) {
    foreach ($evalue as $e_key => $e_value) {
      echo '<li>';
      echo __('Sign', true) . ' -> ';
      echo $this->Html->link(
        trim($e_value['description']->$lang),
        array_merge(
          array(
            'controller' => 'reportnumbers',
            'action' => 'sign',
          ),
          $e_value['reportnumber']
        ),
        array(
          'name' => 'error[Report'.$Verfahren.$ekey.']['.$e_key.']',
          'title' => trim($e_value['description']->$lang),
          'class' => 'error_fields',
          'rel' => $ekey,
          'rev' => 'Report'.$Verfahren.$ekey.Inflector::camelize($e_key),
        )
      );

      echo ' -> ';
      echo trim($__value['message']);

      echo '</li>';
    }
  }
  echo '</ul>';
}
?>
