<?php if(!isset($data['Evaluation'])) return;?>
<li>
<?php echo __('Action',true) . ': ' . __('Evaluation dupliccated',true);?><br>
<?php echo __('Duplicated off',true) . ': ' . $data['Revision']['this_value'];?><br>
<?php echo __('Duplicated to',true) . ': ' . $data['Evaluation']['description'] . '/' . $data['Evaluation']['position'];?><br>
<?php echo __('User',true) . ': ' . $data['Revision']['user'];?> 
<?php echo __('Time',true) . ': ' . $data['Revision']['modified'];?><br>
</li>
