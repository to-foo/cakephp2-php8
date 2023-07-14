<?php
echo(($CountSearchInsertData));
echo '<br>';
print_r($SearchInsertData);
if(isset($stop) && $stop == true){
	echo '
	<script type="text/javascript">
	clearInterval(refreshTimer);
	</script>
	';
	 return false;
}
?>
<script type="text/javascript">
var refreshTimer;
$(document).ready(function() {
	$("#refresh_data_load_container").load("<?php echo Router::url(array_merge(array('action' => 'insertdata'), $this->request->projectvars['VarsArray'])); ?>", {
		"ajax_true": 1
	})
});
</script>
