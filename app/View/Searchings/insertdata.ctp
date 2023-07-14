<div id="refresh_data_load_container">
<?php
echo(($CountSearchInsertData));
echo '<br>';
print_r($SearchInsertData);
echo '<br>';
echo $this->Html->link(__('Load data'), array_merge(array('action' => 'insertdata'),$this->request->projectvars['VarsArray']),array('class' => 'LoadSearchingContent'));
?>
<script type="text/javascript">
$(document).ready(function() {

	$("a.LoadSearchingContent").click(function(){
		$("#refresh_data_load_container").load("<?php echo Router::url(array_merge(array('action' => 'insertdata'), $this->request->projectvars['VarsArray'])); ?>", {
			"ajax_true": 1
		})
		return false;
	});
});
</script>
</div>
<?php  echo $this->JqueryScripte->LeftMenueHeight(); ?>
