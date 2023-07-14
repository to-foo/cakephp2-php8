<div class="flex_info">
<?php

$ReportTable = 'Report' . $this->request->verfahren . $table;

$radiooptions = $this->ViewData->RadioDefault();

foreach ($setting as $_setting) {
  if(empty($reportnumber)) continue;
  if(!isset($reportnumber[trim($_setting->model)])) continue;
  if(!isset($reportnumber[trim($_setting->model)][trim($_setting->key)])) continue;

  if(!isset($reportnumber[trim($_setting->model)])) continue;
  if(!isset($reportnumber[trim($_setting->model)][trim($_setting->key)])) continue;

  $output_value = $reportnumber[trim($_setting->model)][trim($_setting->key)];
  $class = null;

  if (trim($_setting->fieldtype) == 'radio') {

      if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
          $radiooptions = array();

          foreach ($_setting->radiooption->value as $_radiooptions) {
              array_push($radiooptions, trim($_radiooptions));
          }
      }

      $output_value = $radiooptions[(int)$reportnumber[trim($_setting->model)][trim($_setting->key)]];
  } elseif (trim($_setting->fieldtype) == 'checkbox') {
      if ((int)$reportnumber[trim($_setting->model)][trim($_setting->key)] == 1) {
          $output_value = 'X';
      }
  }

  if(!empty($_setting->validate->notempty) && trim($_setting->validate->notempty) == 1 && strlen($output_value) == 0){

    $output_value = '&nbsp;';
    $class = 'notempty ';

  }

  if(empty($output_value)){

    $output_value = '&nbsp;';

  }

  echo '<div class="flex_item ' . $class . '">';
  echo '<div class="label">';
  echo trim($_setting->discription->$locale);
  echo '</div>';
  echo '<div class="content">';
  echo $output_value;
  echo '</div>';
  echo '</div>';

  //			echo $data['Tableshema'][$ReportTable][trim($_setting->key)]['type'] . ' ';
/*
  if (!empty($_setting->validate->notempty) && trim($_setting->validate->notempty) == 1 && strlen($output_value) == 0) {
      echo 'notempty ';
      $output_value = '&nbsp;';
  }

  echo '" ';
  echo 'id="' . $ReportTable . '0' . Inflector::camelize(trim($_setting->key)) . '"';
  echo '>';

  echo '<dd>';
  echo $output_value;
  echo '</dd>';

  echo '<dt>';
  echo $_setting->discription->$lang;
  echo '</dt>';

  echo '</dl>';
*/
}

?>
</div>
