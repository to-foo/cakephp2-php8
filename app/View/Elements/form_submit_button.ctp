<?php
/*
Var action back, close oder reset
Var desctiption Beschriftung Button
*/
if(isset($SettingsArray['backlink'])){
  $url = $this->Html->url(
    array_merge(
      array(
        'controller' => $SettingsArray['backlink']['controller'],
        'action' => $SettingsArray['backlink']['action'],
      ),
      $SettingsArray['backlink']['terms'],
    )
  );
  echo $this->Form->input('UrlLoadLastPage',array('id' => 'UrlLoadLastPage', 'type' => 'hidden','value' => $url));
}

if($action == 'back' && isset($url)) echo $this->Form->input(__('Cancel',true),array('label' => false,'type' => 'button','id' => 'CancelLoadLastPage'));
if($action == 'reset') echo '<div class="button">' . $this->Form->button(__('Cancel',true),array('div' => true,'type' => 'reset','id' => 'CancelResetForm')) . '</div>';
if($action == 'close') echo '<div class="button">' . $this->Form->button(__('Close window',true),array('div' => true,'type' => 'button','id' => 'CloseDialogForm')) . '</div>';

echo $this->Form->end($description);
echo $this->element('js/form_submit_button');
?>
