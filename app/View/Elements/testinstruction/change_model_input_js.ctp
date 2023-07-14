<script type="text/javascript">
$(document).ready(function() {

		$("#TestinginstructionsDataField").on('change', function (e) {
			var selected = $(':selected', this);
			$("#TestinginstructionsDataModel").val(selected.closest('optgroup').attr('label'));
		});

		$("#TestinginstructionsDataType").on('change', function (e) {
			if($(this).val() == 2){
				$("#TestinginstructionsDataValue").addClass("error");
				if($("#TestinginstructionsDataValue").val().length == 0){
					submit = $("#TestinginstructionsDataValue").closest('form').find(':submit');
					submit.attr("disabled","disaled");
				}
			}
			else {
				$("#TestinginstructionsDataValue").removeClass("error");
				submit = $("#TestinginstructionsDataValue").closest('form').find(':submit');
				submit.attr("disabled",false);
			}
		});

		$("#TestinginstructionsDataValue").on('change', function (e) {
			if($(this).val().length > 0){

				$("#TestinginstructionsDataValue").removeClass("error");
				submit = $("#TestinginstructionsDataValue").closest('form').find(':submit');
				submit.attr("disabled",false);

			}
		});

});
</script>
