<?php
if(Configure::read('TemplateManagement') == false) return;

echo $this->Html->link(__('Select evaluation template',true),
  array_merge(
    array(
      'controller'=>'templates',
      'action'=>'get'
    ),
    $this->request->projectvars['VarsArray']
  ),
  array(
    'class'=>'round modal',
    'title' => __('Select evaluation template',true)
  )
);

echo $this->element('templates/js/select_dropdown_evaluations');
?>
