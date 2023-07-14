<?php
if(count($this->request->data['Welds']) == 0) return;

$setting = $evaluationoutput;
$lang = $locale;

$i = 0;

$ReportPdf  = $this->request->data['Tablenames']['Pdf'];
$Modelpart  = $this->request->data['Tablenames']['Evaluation'];;
foreach ($this->request->data['Welds'] as $key => $dataArray) {

  if (isset($reportnumber['RevisionValues'][$Modelpart][$dataArray[$Modelpart]['weld'][0]['id']])) $revtrue = 1;
  else $revtrue = 0;

  $comp_weld = '1';

  echo '<tbody class="weld weld_'.$dataArray[$Modelpart]['discription'].'">';

  echo $this->element('data/evaluation_data_table_body_weldhead',array('dataArray' => $dataArray,'evalDiscription'  => $evalDiscription,'revtrue' => $revtrue,'key' => $key));

  echo $this->element('data/evaluation_data_table_body_data',array('dataArray' => $dataArray,'evalDiscription'  => $evalDiscription,'revtrue' => $revtrue,'comp_weld' => $comp_weld,'key' => $key));

  echo '</tbody>';
}
?>
