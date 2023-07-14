<?php
if(!isset($this->request->data['BreadcrumpArray'])) return;
if(count($this->request->data['BreadcrumpArray']) < 1) return;
echo '<h5>';

foreach ($this->request->data['BreadcrumpArray'] as $key => $value) {

  echo $this->Html->link($value['title'],
    array_merge(
      array(
        'controller' => $value['controller'],
        'action' => $value['action']
      )
      ,$value['params']
    ),
    array(
      'title' => $value['title'],
      'class' => $value['class']
    )
  );

  if(!isset($value['end'])) echo ' > ';

}

echo '</h5>';
?>
