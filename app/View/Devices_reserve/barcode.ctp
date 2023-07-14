<div class="<?php echo $message['class'];?>"><p>
<?php echo $message['text'];?>
</p></div>
<?php
if($message['status'] == 'one'){ 
$url = $this->Html->url(array_merge(array('action' => 'view'),$this->request->projectvars['VarsArray']));
echo '<script type="text/javascript">';
echo '$(document).ready(function(){$("#dialog").load("'.$url.'", {"ajax_true": 1})});';
echo '</script>';
}
if($message['status'] == 'more'){
	echo '<div class="hint"><p>';
	foreach($device as $_device){
		$this->request->projectvars['VarsArray'][15] = $_device['DeviceTestingmethod'][0]['id'];
		$this->request->projectvars['VarsArray'][16] = $_device['Device']['id'];
		echo $this->Html->link($_device['Device']['name'],array_merge(array('action' => 'view'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal round'));
	}
	echo '</p></div>';

	echo '<script type="text/javascript">';
	echo '$(document).ready(function(){';
	echo '$(".mymodal").click(function() {';
	echo '$("#dialog").load($(this).attr("href"), {"ajax_true": 1});';
	echo 'return false;';
	echo '});';
	echo '});';
	echo '</script>';

}
?>