<?php 
if(isset($this->request->data['Template'])) return; 
if(isset($EditUrl)) return; 
?>

<?php echo $this->Form->create('ExpeditingTemplate', array('class' => 'login','autocomplete' => 'off')); ?>
<?php

$editlink = $this->Html->link('Edit',
  array_merge(array('controller' => 'expeditings', 'action' => 'edittemplate'), $this->request->projectvars['VarsArray']),
    array_merge(
      array(
        'class' => 'icon icon_edit edit_expediting_template',
        'title' => __('Edit this Template', true),

    )
  )
);

$Options = array(
    'options' => $Expeditingset,
 //   'after' => $editlink
);

echo '<fieldset>';
echo $this->Form->input('Template', $Options);
echo '</fieldset>';
?>
<?php 
echo $this->element('expediting/js/edit_template_link');
echo $this->element('form_submit_button',array('description' => __('Choose',true),'action' => 'close'));
?>