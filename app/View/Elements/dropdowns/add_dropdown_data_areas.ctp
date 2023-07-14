<?php
echo '<div class="hint"><p>';
echo $this->Html->link(__('Add Dropdown Data'),array_merge(array('action' => 'masteradddata'),$this->request->projectvars['VarsArray']),array('title' => __('Add Testinginstruction Data'),'class' => 'round modal_post'));
echo '</p></div>';
?>
