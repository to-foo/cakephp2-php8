<?php if(Configure::check('StopMasterMassAction') == true && Configure::read('StopMasterMassAction') == true) return;?>
  
<div class="hint">
  <?php
  echo 	$this->Form->input(
    'mass_function_report_table_start',
    array(
      'id' => 'mass_function_report_table_start',
      'div' => false,
      'label' => false,
      'options' => array(
        0 => __('Please select',true),
        'sign' => __('Sign',true),
        'close_2' => __('Closing',true),
      )
    )
  );

  ?>
</div>
