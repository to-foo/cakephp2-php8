<?php
$ReportPdf  = $this->request->data['Tablenames']['Pdf'];
$setting = $evaluationoutput;
$lang = $locale;

echo '<thead>';

if ($this->viewVars['replaceHeaderSetting'] == '1' || $this->viewVars['replaceHeaderSetting'] == 'true') {
  echo '<tr>';
  echo '<th colspan="'.(count($evalDiscription)+3).'" class="hint">'.__('Attention: Entries in first row will be used for titles.').'</th>';
  echo '</tr>';
}

echo '<tr>';
echo '<th>';
echo $this->Form->input('all_welds', array('class' => 'reportnumber_all_welds','type' => 'checkbox','label' => false));
echo '</th>';
echo $evalHeaders;
echo '</tr>';
echo '</thead>';
?>
