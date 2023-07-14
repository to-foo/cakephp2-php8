<?php
if(count($this->request->data['BreadcrumpArray']) < 1) return;

echo '<h5>';

echo $this->Html->link('Home',
  array_merge(
    array(
      'action' => 'edit'
    )
    ,$this->request->projectvars['VarsArray']
  ),
  array(
    'title' => 'Home',
    'class' => 'ajax home_link'
  )
);

foreach ($this->request->data['BreadcrumpArray'] as $key => $value) {

  if($key == 0) continue;

  echo $this->Html->link($value['title'],
    array_merge(
      array(
        'controller' => $value['controller'],
        'action' => $value['action']
      )
      ,$value['params']
    ),
    array(
      'rel' => $value['data']['scheme_start'],
      'rev' => (isset($value['data']['cascade_group_id']) ? $value['data']['cascade_group_id'] : ''),
      'title' => $value['title'],
      'class' => $value['class']
    )
  );

  if(!isset($value['end'])) echo ' > ';
}

echo '</h5>';
?>
