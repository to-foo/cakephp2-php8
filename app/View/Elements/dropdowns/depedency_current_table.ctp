<?php
if(!isset($DependencyFieldAfterSaving)) return;
echo $this->Form->input('DependencyFieldAfterSaving',array('type' => 'hidden','value' => $DependencyFieldAfterSaving));
?>

<script type="text/javascript">
$(document).ready(function() {
	key = $("#DependencyFieldAfterSaving").val();
	$("div.dependency_table").hide();
	$("div." + key).show();

});
</script>
