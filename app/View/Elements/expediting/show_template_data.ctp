<?php
if(!isset($this->request->data['Template'])) return;
if(count($this->request->data['Template']) == 0) return;
?>
<div class="flex_info_column">
<?php
foreach ($this->request->data['Template'] as $key => $value) {

    echo '<div class="flex_item">';
 
    echo $value['Expediting']['description'];
    echo ' - ';
    echo $value['Expediting']['hold_witness_point_description'];

    if(isset($value['Expediting']['date_soll']) && !empty($value['Expediting']['date_soll'])){

        echo '<div class="flex_info">';
        echo '<div>';
        echo __('Soll Date') . ': ' . $value['Expediting']['date_soll'];
        echo '</div>';
        echo '<div>';
        echo __('created from') . ': ';
        echo __($value['Expediting']['period_datum']) . ' ';
        echo '(' . $this->request->data['Supplier'][$value['Expediting']['period_datum']] . ') ';
        echo $value['Expediting']['period_sign'] . $value['Expediting']['period_time'] . ' ';

        if($value['Expediting']['period_time'] == 1) $period_measure = Inflector::singularize($value['Expediting']['period_measure']);
        else $period_measure = Inflector::pluralize($value['Expediting']['period_measure']);

        echo __($period_measure);
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
    echo '</div>';

}
?>
</div>
<?php echo $this->Form->create('ExpeditingTemplate', array('class' => 'login','autocomplete' => 'off')); ?>
<?php
echo '<fieldset>';
echo $this->Form->input('id', array('type' => 'hidden','value' => $this->request->data['Expeditingset']['id']));
echo '</fieldset>';
?>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'back'));?>