<?php
$field = trim($_setting->key);
$modelpart = trim($_setting->model);

if(!isset($data[$modelpart]['id'])) return;

$id = $data[$modelpart]['id'];

if($data['Reportnumber']['revision'] == 0) return;
if(count($data['RevisionValues']) == 0) return;
if(!isset($data['RevisionValues'][$modelpart])) return;
if(!isset($data['RevisionValues'][$modelpart][$id])) return;
if(!isset($data['RevisionValues'][$modelpart][$id][$field])) return;

$revisionlink = $this->Html->link('Showrevisions',
  array_merge(array('controller' => 'reportnumbers', 'action' => 'showrevisions'), $this->request->projectvars['VarsArray']),
    array_merge(
      array(
        'class' => 'tooltip_ajax_revision',
        'title' => __('Content will load...', true),
        'id' => $modelpart . '/' . $field
    )
  )
);

//echo $revisionlink;

?>
