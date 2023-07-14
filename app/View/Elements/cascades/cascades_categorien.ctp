<?php
echo '<ul class="listemax">';
foreach ($Cascade['Cascadegroups'] as $key => $value) {
  echo '<li>';
  echo $this->Html->link($Cascade['CascadegroupsName'][$key], array_merge(array('action' => 'index'),$this->request->projectvars['VarsArray']), array('rel' => $key,'class' => 'show_cascade_group','title' => $Cascade['CascadegroupsName'][$key]));
  echo '</li>';
}
echo '</ul>';
/*
pr($Cascade['CascadegroupsName']);
pr($Cascade['Cascadegroups']);
*/
?>
<?php echo $this->element('cascades/js/change_cascade_js');?>
