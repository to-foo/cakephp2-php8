<?php
echo '<div class="hint">';
echo $this->Html->link(__('Add evaluation template',true),array_merge(array('controller'=>'templates','action'=>'evaluation'),$this->request->projectvars['VarsArray']),array('class'=>'modal round','title' => __('Add evaluation template',true)));
echo '</div>';

if(!isset($this->request->data['TemplatesEvaluation'])) return;
if(empty($this->request->data['TemplatesEvaluation'])) return;
?>

<table class="advancetool">
<tr>
<th></th>
<th><?php echo __('Name',true);?></th>
<th><?php echo __('Testingmethod',true);?></th>
<th><?php echo __('Description',true);?></th>
</tr>
<?php

foreach ($this->request->data['TemplatesEvaluation'] as $key => $value) {

  $Model = key($value);
  $this->request->projectvars['VarsArray'][1] = $value[$Model]['id'];

  echo '<tr>';
  echo '<td>';
  echo $this->Html->link(__('Edit',true),array_merge(array('controller'=>'templates','action'=>'evaluation'),$this->request->projectvars['VarsArray']),array('class'=>'icon icon_edit modal','title' => __('Edit',true)));
  echo $this->Html->link(__('Delete',true),array_merge(array('controller'=>'templates','action'=>'json'),$this->request->projectvars['VarsArray']),array('rel' => $value[$Model]['id'],'class'=>'icon icon_del delete_evaluation_template','title' => __('Should this value be deleted?',true)));
  echo '</td>';
  echo '<td><p class="editable" rev="name" rel="'.$value[$Model]['id'].'">' . $value[$Model]['name'] . '</p></td>';
  echo '<td>' . $value[$Model]['testingmethod'] . '</td>';
  echo '<td><p class="editable" rev="description" rel="'.$value[$Model]['id'].'">' . $value[$Model]['description'] . '</p></td>';
  echo '</tr>';

  $this->request->projectvars['VarsArray'][1] = 0;

}

?>
</table>
