<?php echo $this->Form->create('Expeditingset', array('class' => 'login','autocomplete' => 'off')); ?>
<?php
echo $this->Form->input('id');
echo '<fieldset>';
echo $this->Form->input('name');
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('description');
echo '</fieldset>';
?>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'back'));?>

<?php

if(!isset($this->request->data['Template'])){

echo '<div class="flex_info_column">';
echo '<div class="flex_item">';
echo '<div class="flex_info">';

$VarsArray = $this->request->projectvars['VarsArray'];

echo $this->Html->link(__('Add expediting step',true),
array_merge(
  array(
    'controller'=>'expeditings',
    'action' => 'addexpeditingstep'),
    $VarsArray
),
array(
  'title' => __('Add expediting step',true),
  'class'=>'round mymodal',
 )
);

echo '</div>';
echo '</div>';
echo '</div>';

return;

}
?>

<div class="flex_info_column">
<?php

foreach ($this->request->data['Template'] as $key => $value) {

    echo '<div class="flex_item">';
 
    echo $value['Expediting']['description'];
    echo ' - ';
    echo $value['Expediting']['hold_witness_point_description'];

    if(isset($value['Expediting']['period_datum']) && !empty($value['Expediting']['period_datum'])){

        echo '<div class="flex_info">';
        echo '<div>';
        echo __($value['Expediting']['period_datum']) . ' ';
        echo $value['Expediting']['period_sign'] . $value['Expediting']['period_time'] . ' ';

        if($value['Expediting']['period_time'] == 1) $period_measure = Inflector::singularize($value['Expediting']['period_measure']);
        else $period_measure = Inflector::pluralize($value['Expediting']['period_measure']);

        echo __($period_measure);
        echo '</div>';
        echo '<div>';
        echo __('Karenz') . ': ' . $value['Expediting']['karenz'] . ' ' . __('Days');
        echo '</div>';
        echo '</div>';

    } else {

        echo '<div class="flex_info">';
        echo '<div>';
        echo __('No rules were stored for a target date.');
        echo '</div>';
        echo '</div>';

    }
    
    echo '<div class="flex_info">';
    echo '<b>' . __('Involved user roles') . '</b><br>';

    foreach ($value['Rollname'] as $_key => $_value) {
        echo '<div>';
        echo $_value;
        echo '</div>';
    }
 
    echo '</div>';

    $VarsArray = $this->request->projectvars['VarsArray'];

    $VarsArray[3] = $this->request->data['Expeditingset']['id'];
    $VarsArray[4] = $value['Expediting']['id'];

    echo $this->Html->link(__('Edit this expediting step',true),
    array_merge(
      array(
        'controller'=>'expeditings',
        'action' => 'editexpeditingstep'),
        $VarsArray
    ),
    array(
      'title' => __('Edit this expediting step',true),
      'class'=>'round mymodal',
     )
    );
  

    echo '</div>';

}
?>
<div class="flex_item">
<div class="flex_info">
<?php

$VarsArray = $this->request->projectvars['VarsArray'];

echo $this->Html->link(__('Add expediting step',true),
array_merge(
  array(
    'controller'=>'expeditings',
    'action' => 'addexpeditingstep'),
    $VarsArray
),
array(
  'title' => __('Add expediting step',true),
  'class'=>'round mymodal',
 )
);
?>
</div>
</div>
</div>
</div>