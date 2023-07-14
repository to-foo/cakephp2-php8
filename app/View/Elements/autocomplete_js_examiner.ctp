<script type="text/javascript">
$(document).ready(function(){
	// Autocomplete JS Start
	$("#CascadeEditForm input[type=text]").each(function(key,value){
		if ($(this).attr("autocomplete") == 'autocomplete' && $(this).is(":visible")){
		
			value = $(value);

			value.autocomplete({
				minLength: 2,
				delay: 4,
				close: function() {
					$(this).autocomplete("close");
				},
				source: function(request,response) {
					$.ajax({
					url: "<?php echo $this->Html->url(array('action' => 'autocomplete'));?>",
					dataType: "json",
					data: {
						term : request.term,
						field : $(this).attr("autocompletefield"),
						model : $(this).attr("autocompletemodel"),
						},
					success: function(data) {
						response(data);
						},
					});
				}
			});


		}
	});
	// Autocomplete JS Ende
});
</script>
