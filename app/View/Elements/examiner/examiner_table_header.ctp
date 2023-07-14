<?php
if(Configure::check('ExaminerManagerTableQualification') == true && Configure::read('ExaminerManagerTableQualification') == true)echo '<th>' . __('Qualifications',true) . '</th>';
if(Configure::check('ExaminerManagerTableEyecheck') == true && Configure::read('ExaminerManagerTableEyecheck') == true)echo '<th>' . __('Eye checks',true) . '</th>';
if(Configure::check('ExaminerManagerTableMonitoring') == true && Configure::read('ExaminerManagerTableMonitoring') == true) echo '<th class="small_cell">' . __('Monitorings',true) . '</th>';
?>
