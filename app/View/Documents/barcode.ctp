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
	foreach($document as $_document){
		$this->request->projectvars['VarsArray'][15] = $_document['DocumentTestingmethod'][0]['id'];
		$this->request->projectvars['VarsArray'][16] = $_document['Document']['id'];
		echo $this->Html->link($_document['Document']['name'],array_merge(array('action' => 'view'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal round'));
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