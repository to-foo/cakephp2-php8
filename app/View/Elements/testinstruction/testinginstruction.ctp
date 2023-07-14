<?php
if(!isset($this->request->testinstruction)) return;

	if(count($this->request->testinstruction->deviation) == 0) $Headline = __('No deviations detected',true);
	else $Headline = __('Deviations detected',true);
/*
	echo '<div class="testinstruction_error_link">';
	echo '<b>' . __('Testing instructions',true) . '</b><br>';
	echo '<span class="count"></span>';
	echo ' ' . __('irregularities',true);
	echo '</div>';
*/

	echo '<div class="testinstruction_error">';
	echo $this->Html->link('test','javascript:',array('title' => __('Close window',true),'class' => 'close_window'));
	echo '<p>';
	echo '<b>' . $Headline . '</b>';
	echo '</p>';
	echo '<ul class="model">';

	if(isset($this->request->testinstruction->deviation) && count($this->request->testinstruction->deviation) > 0){
		foreach($this->request->testinstruction->deviation as $_key => $_data){
			foreach($_data as $__key => $__data){
			echo '<li class="main" id="Dev'.$_key.Inflector::camelize($__key).'">';
				echo '<b>' . trim($settings->$_key->$__key->discription->$locale) . '</b>';
				echo '<ul class="field">';
				echo '<li>' . $__data['value']['right'] . '<br><span class="label">' . __('Value according to test specification',true) . '</span></li>';
				echo '<li>' . $__data['value']['wrong'] . '<br><span class="label">' . __('Current value in this testing report',true) . '</span></li>';

				if(isset($this->request->testinstruction->data[$_key][$__key]['reason'])){
					echo '<li><span class="round editable_reason" data-id="' . $this->request->testinstruction->data[$_key][$__key]['data_id'] . '">';
					echo $this->request->testinstruction->data[$_key][$__key]['reason'];
					echo '</span></li>';
				} else {
					echo '<li><span class="round editable_reason" data-id="' . $this->request->testinstruction->data[$_key][$__key]['data_id'] . '">' . __('Reason',true) . '</span></li>';
				}
				echo '</ul>';
				echo '</li>';
			}
		}
	}
	echo '</ul>';
	echo '</div>';

echo $this->element('testinstruction/testinginstruction_js');
?>
