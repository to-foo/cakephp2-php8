<table class="advancetool">
<thead>
<?php

$radiooptions = $this->ViewData->RadioDefault();

echo '<tr>';

$x = 0;

foreach ($evaluationoutput as $key => $value) {

  if (trim($value->showintable) == 1) {

      $colspan = $x == 0 ? ' colspan="2"' : null;

      echo '<th' . $colspan . '>' . $value->discription->$locale . '</th>';

      $x++;
  }
}

$x++;
$i = 0;

echo '</tr>';
echo '</thead>';

echo $this->element('reports/report_view_evaluationdata_tablecontent',array('x' => $x,'i' => $i));

?>

</table>
