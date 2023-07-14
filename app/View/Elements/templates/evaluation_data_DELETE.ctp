<?php
if(!isset($this->request->data['Template']['evaluation_temp'])) return;
if(count($this->request->data['Template']['evaluation_temp']) == 0) return;

echo '<div class="hint">';
echo $this->Html->link(__('Add evaluation template',true),array_merge(array('controller'=>'templates','action'=>'evalution'),$this->request->projectvars['VarsArray']),array('class'=>'modal round','title' => __('Add evaluation template',true)));
echo '</div>';
$lang = $locale;
$setting = $this->request->data['Template']['xml']['settings']->ReportRtEvaluation;
?>
<table class="advancetool">
<tr>
<?php
echo '<th>';
echo '</th>';

$x = 0;
$Fields = array();

foreach ($setting->children() as $_setting) {

  if(empty($_setting->output->screen)) continue;
  if(trim($_setting->output->screen) != 4) continue;

  $Model = trim($_setting->model);
  $Field = trim($_setting->key);
  $Desk = trim($_setting->discription->$lang);

  if($Field == 'result' || $Field == 'rep_area' || $Field == 'error'  || $Field == 'remark') continue;

  echo '<th>';
  echo $Desk;
  echo '</th>';

  $x++;

  if($Field != 'description') $Fields[$x] = $Field;

}
?>
</tr>

<tr>
<?php
$Count = 1;

echo '<td>';
echo '</td>';

$x = 0;
$Fields = array();

foreach ($setting->children() as $_setting) {

  if(empty($_setting->output->screen)) continue;
  if(trim($_setting->output->screen) != 4) continue;

  $Model = trim($_setting->model);
  $Field = trim($_setting->key);
  $Desk = trim($_setting->discription->$lang);

  if($Field == 'result' || $Field == 'rep_area' || $Field == 'error'  || $Field == 'remark') continue;

  echo '<td id="table_cell_'.$Count.'">';
  echo '<p class="editable edit_complete_template" data-weld="0" data-field="' . $Field . '">';
  echo ' ';
  echo '</p>';
  echo '</td>';

  $x++;
  $Count++;

  if($Field != 'description') $Fields[$x] = $Field;

}
?>
</tr>
</table>
