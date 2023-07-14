<?php
if(!isset($this->request->data[$type])) return;
if(empty($this->request->data[$type])) return;
?>
<div class="hint">
<table class="advancetool">
<thead>
<tr>
<th><?php echo __('Test reports already created');?></th>
</tr>
</thead>
<tbody>
<tr>
<td>
<div class="advance_diagrammcontent">
  <div class="div_plot_container">
<?php
foreach ($this->request->data[$type]['Reportnumbers'] as $key => $value) {

  if(count($value['TicketReportnumber']) == 2) continue;

  echo '<div class="table_result">';
  echo '<div class="editable_content">';
  echo'<p><b>';
  echo $value['Reportnumber']['year'] . '-' . $value['Reportnumber']['number'] . ' (' . $value['TicketReportnumber']['name'] . ')';
  echo '</b></p>';
  echo '</div>';

  echo $this->Html->link(__('Edit report', true),array_merge(array('controller' => 'reportnumbers','action' => 'edit'),$value['Reportnumber']['url']),array('title' => __('Edit report', true),'class' => 'icon icon_edit ajax'));
  echo $this->Html->link(__('Sign report', true),array_merge(array('controller' => 'reportnumbers','action' => 'sign'),$value['Reportnumber']['url']),array('title' => __('Sign report', true),'class' => 'icon icon_sign ajax'));

  $value['Reportnumber']['url'][5] = 0;
  $value['Reportnumber']['url'][6] = 0;
  $value['Reportnumber']['url'][7] = 0;
  $value['Reportnumber']['url'][8] = 3;

  echo $this->Html->link(__('Print report', true),array_merge(array('controller' => 'reportnumbers','action' => 'pdf'),$value['Reportnumber']['url']),array('title' => __('Print report', true),'class' => 'icon icon_print showpdflink'));
  echo $this->Html->link(__('Report status', true),'javascipt:',array('title' => __('Report status', true),'class' => 'icon ' . $value['Reportnumber']['status_class']));

  if(isset($value['Evaluation']) && !empty($value['Evaluation'])){
    echo '<div class="editable_content">';

    foreach ($value['Evaluation'] as $_key => $_value) {

      $title = __('Sheet no.') . ': ' . $_value['sheet_no'] . ' ' . __('Spool') . ' ' . $_value['spool_id'];

      echo '<span title ="'.$title.'">';
      echo $_value['description'];
      echo '</span> ';
    }

    echo '</div>';
  }

  echo '<div class="editable_content">';

  if($value['Reportnumber']['status'] == 0) echo '<p class="editable edit_testingcomp" data-id="' . $value['Reportnumber']['id'] . '" title="' . __('Click to change testingcompany') . '">';
  else echo '<p class="editable">';

  echo $value['Testingcomp']['name'];
  echo '</p>';
  echo '</div>';
  echo '</div>';

}

?>
</div>
</div>
</td>
</tr>
</tbody>
</table>
</div>
