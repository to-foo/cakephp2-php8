<?php
if(!isset($this->request->data['TemplatesEvaluation'])) return;
if(empty($this->request->data['TemplatesEvaluation'])) return;

echo '<div class="hint">';
echo '</div>';
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

  $this->request->projectvars['VarsArray'][5] = $value['TemplatesEvaluation']['templates_id'];
  $this->request->projectvars['VarsArray'][6] = $value['TemplatesEvaluation']['id'];

  echo '<tr>';
  echo '<td>';

  echo $this->Html->link(__('Show',true),
    array_merge(
      array(
        'controller'=>'templates',
        'action'=>'show'
      ),
        $this->request->projectvars['VarsArray']
      ),
      array(
        'class'=>'icon icon_view mymodal',
        'title' => __('Show template',true)
      )
    );

  echo '</td>';
  echo '<td>' . $value['TemplatesEvaluation']['name'] . '</td>';
  echo '<td>' . $value['TemplatesEvaluation']['testingmethod_id'] . '</td>';
  echo '<td>' . $value['TemplatesEvaluation']['description'] . '</td>';
  echo '</tr>';

  $this->request->projectvars['VarsArray'][5] = 0;
  $this->request->projectvars['VarsArray'][6] = 0;

}

?>
</table>
