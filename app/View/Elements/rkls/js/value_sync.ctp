<script type="text/javascript">
$(document).ready(function(){

	var SearchFormName = "#<?php echo $name;?>";

	$(SearchFormName  + " select").on("change", function() {

		var id = $(this).val();
		var field_class = "." + $(this).closest("fieldset").attr('class');

		if (!id) {
			$(field_class + " select.hasone").val("");
		} else {
			$(field_class + " select.hasone option[value='" + id + "']").attr('selected',true);
		}

	});
});
</script>
