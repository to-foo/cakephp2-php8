<?php
	$TimeStatus = null;

	if($this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] == 1 && $this->request->data['Status']['all_advance_methods']['result']['advance_rep_deci'] == 0){
	  $TimeStatus = '<p class="time_status success">';
	  $TimeStatus .= __('Work on this area is complete',true);
	  $TimeStatus .= '</p>';
	}

	if($this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] == 1 && $this->request->data['Status']['all_advance_methods']['result']['advance_rep_deci'] > 0){
	  $TimeStatus = '<p class="time_status error">';
	  $TimeStatus .= __('Work on this area is complete with repairs',true);
	  $TimeStatus .= '</p>';
	}

	foreach ($this->request->data['AdvancesCascade']['status'] as $key => $value) {
		if($this->request->data['Status']['all_advance_methods']['result']['advance_all_deci'] == 1) continue;

		if($key == 'end_delay' && $value == 1){
			$TimeStatus = '<p class="time_status error">';
			$TimeStatus .= __('The work on this area is delayed',true);
			$TimeStatus .= '</p>';
			break;
		}

		if($key == 'end' && $value == 1){
			$TimeStatus = '<p class="time_status delay">';
			$TimeStatus .= __('The work on this area is delayed',true);
			$TimeStatus .= '</p>';
			break;
		}

		if($key == 'start' && $value == 0){
			$TimeStatus = '<p class="time_status future">';
			$TimeStatus .= __('Work on this area will start on',true) . ' ' . $this->request->data['AdvancesCascade']['start'];
			$TimeStatus .= '</p>';
			break;
		}

		if($key == 'start' && $value == 1){
			$TimeStatus = '<p class="time_status intime">';
			$TimeStatus .= __('Work on this area began on',true) . ' ' . $this->request->data['AdvancesCascade']['start'];
			$TimeStatus .= '</p>';
		}

	}

	echo $TimeStatus;

?>
