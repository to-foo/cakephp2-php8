<?php
$DataModel = $this->request->data['Template']['models'][2];
$lang = $locale;
$setting = $this->request->data['Template']['xml']['settings']->$DataModel;
?>
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

<?php
foreach ($this->request->data['Template']['data'][$DataModel] as $key => $value) {

  echo '<tr weld-data"' . $key . '">';
  echo '<td class="weldhead">';

  echo $this->Html->link(__('Delete',true),'javascript:',array('class'=>'icon icon_delete','rel' => $key,'title' => __('Delete',true)));
  echo $this->Html->link(__('Duplicate',true),'javascript:',array('class'=>'icon icon_dupli','rel' => $key,'title' => __('Duplicate',true)));

  echo '</td>';

  echo '<td class="weldhead" id="table_cell_'.$Count.'">';
  echo '<p class="editable">';
  echo $key;
  echo '</p>';
  echo '</td>';

  $Count++;

//  for ($i=2; $i < $x; $i++) {
  foreach ($Fields as $_key => $_value) {


    echo '<td id="table_cell_'.$Count.'">';
    echo '<p class="editable edit_complete_weld" data-weld="' . $key . '" data-field="' . $_value . '" data-count="' . $_key . '">';
    echo ' ';
    echo '</p>';
    echo '</td>';

    $Count++;
  }

  echo '</tr>';

  foreach ($value as $_key => $_value) {

    echo '<tr weld-data"' . $_value['description'] . '">';

    echo'<td>';

    echo $this->Html->link(__('Delete',true),'javascript:',array('class'=>'icon icon_delete delete_pos','rel' => $key, 'rev' => $_key,'title' => __('Delete',true)));

    echo $this->Html->link(__('Duplicate',true),
      array_merge(array(
        'controller' => 'templates',
        'action' => 'json',
      ),$this->request->projectvars['VarsArray']),
      array(
        'class'=>'icon icon_dupli dupli_pos',
        'rel' => $key,
        'rev' => $_key,
        'title' => __('Duplicate',true)
      )
    );

    echo '</td>';

    foreach ($_value as $__key => $__value) {
      echo '<td class="weld" id="table_cell_'.$Count.'">';
      echo '<p class="editable edit_complete_position" data-weld="' . $key . '" data-position="' . $_key . '" data-field="' . $__key . '">';
      echo $__value;
      echo '</p>';
      echo '</td>';

      $Count++;

    }

    echo '</tr>';
  }
}
?>
