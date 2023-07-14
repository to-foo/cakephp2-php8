<ul class="editmenue">
<?php
foreach($editmenue as $_editmenue){

  $_editmenue = array_merge(array(
    'class'=>null,
    'id'=>null,
    'discription' => null,
    'controller' => $this->request->params['controller'],
    'action' => $this->request->params['action'],
    'parms' => $this->request->projectvars['VarsArray']
  ), $_editmenue);

  echo '<li class="'.$_editmenue['class'].'" id="Li_'.$_editmenue['id'].'">';

  echo $this->Html->link($_editmenue['discription'],
    array_merge(
      array(
        'controller' => $_editmenue['controller'],
        'action' => $_editmenue['action'],
      ),$this->request->projectvars['VarsArray']
    ),
    array(
      'class' => $_editmenue['rel'].' '.$_editmenue['class'],
      'id' => $_editmenue['id'],
      'rel' => $_editmenue['rel']
      )
    );

  echo '</li>';
}
?>
</ul>
