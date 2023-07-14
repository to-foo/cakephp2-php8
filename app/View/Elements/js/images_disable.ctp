<?php
if($attribut_disabled === false) return;
?>
<script>
	$(function() {
		$("form#OrderImagesForm input, form#OrderImagesForm select, form#OrderImagesForm textarea, form#OrderImagesForm button").attr("disabled", "disabled");
	});
</script>
