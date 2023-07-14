<?php
echo 	$this->Form->input(
        'mass_function_report_table_' . $reportnumber,
          array(
            'type' => 'checkbox',
            'class' => 'check_weld',
            'label' => ' ',
            'value' => $reportnumber,
          )
        );

?>
