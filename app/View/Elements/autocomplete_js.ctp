<script type="text/javascript">
$(document).ready(function(){	
	// Autocomplete JS Start
	$("#DeviceSearchForm input[type=text]").each(function(key,value){
		if ($(this).attr("name").indexOf("data[Device]") == 0 && $(this).is(":visible")){
			
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
						field: $(value.context).attr("id")
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