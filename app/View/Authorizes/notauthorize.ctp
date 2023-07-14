<div class="error"><p>
<?php echo __('No rights for this function,', true);?>
<br />
<?php echo __('this window will close in ', true) . $timeout / 1000 . ' seconds';?>

</p></div>
<?php
echo '
<script type="text/javascript">
	$(document).ready(function(){
		
		if ($("#dialog").hasClass("ui-dialog-content")) {
			if ($("#dialog").dialog("isOpen") === true) {
				setTimeout("$(\'#dialog\').dialog(\'close\');",'.$timeout.'); 
			} else {
				setTimeout("$(\'#container\').load(\'' . $ajax_url . '\', {\'ajax_true\': 1});",'.$timeout.');
			}
		} else {
			setTimeout("$(\'#container\').load(\'' . $ajax_url . '\', {\'ajax_true\': 1});",'.$timeout.');
		}
		
	});
</script>
';?>