<?php echo $this->element('rest/js/barcode_scanner');?>
<div class="status">
<?php echo '<div id="notes_request_message" class="notes_request_message">' . __("Waiting for input",true) . '</div>';?>
</div>
<div class="types">
<span>
<?php echo __("If you want to scan the barcode with a mobile device or your computer's camera, you can start the camera here.");?>
</span>
<div>
<?php	
echo $this->Html->link(__('Start'),'javascript:',
	array(
		'id' => 'startButton',
		'title' => __('Open device camera',true),
		'class' => 'round'
	)
);

echo $this->Html->link(__('Close'),'javascript:',
	array(
		'id' => 'resetButton',
		'title' => __('Close device camera',true),
		'class' => 'round'
	)
);
?>
</div>

<div>
<video id="video" class="video_container" style="display:none"></video>
</div>

<div id="sourceSelectPanel" style="display:none">
<label for="sourceSelect">Change video source:</label>
<select id="sourceSelect" style="max-width:400px">
</select>
</div>
<pre><code id="result"></code></pre>
</div>
<div class="types">
<?php
echo '<div class="notes_summary">';
echo __('If a barcode scanner is connected to the current device, the barcode can be scanned here.');
echo '</div>';
echo '<span class="notes_summary" id="notes_summary">';
echo '</span>';

echo $this->Html->link('Barcode Scanner','javascript:',
	array(
		'id' => 'notes_input_link',
		'title' => __('Scann function is deactiv.',true),
		'class' => 'icon_large icon_barcode icon_barcode_deactive notstop'
	)
);

echo '<div id="hidden_for_you">';
echo $this->Form->textarea('notes_input');

echo $this->Form->input('update_url',
	array(
		'type' =>'hidden',
		'label' => false,
		'div' => false,
		'value' => $this->Html->url(array_merge(array('controller' => 'rests','action' => 'getspooldata'),$this->request->projectvars['VarsArray'])),
	)
);

echo $this->Form->input('tickets_url',
	array(
		'type' =>'hidden',
		'label' => false,
		'div' => false,
		'value' => $this->Html->url(array_merge(array('controller' => 'rests','action' => 'tickets'),$this->request->projectvars['VarsArray'])),
	)
);

echo '</div>';

echo $this->Form->input('message',
	array(
		'label' => false,
		'div' => false,
		'value' => '',
		'class' => 'hidden'
	)
);
?>
</div>
<div class="types">
<span>
<?php echo __('If you have the ticket ID from the barcode, you can enter it here and send it off.');?>
</span>
<?php
echo $this->Form->input('ticket_number',
	array(
		'label' => false,
		'div' => false,
	)
);
?>
</div>

