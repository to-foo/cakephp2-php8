<?php
if(!isset($timeout)) $_timeout = 0;
else $_timeout = $timeout;

$url_test = explode('/',$ajax_url);

echo $this->Form->input('UrlTest',array('type' => 'hidden','value' => $ajax_url));
echo $this->Form->input('TimeOut',array('type' => 'hidden','value' => $_timeout));
?>
<script type="text/javascript">
	$(document).ready(function(){

		timeout = function(){
			$("#container").load($("#UrlTest").val(),{"ajax_true": 1});
		}

		setTimeout(timeout,$("#TimeOut").val());

	});
</script>
