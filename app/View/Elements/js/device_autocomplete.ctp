<?php
	echo $this->Form->create('Device',array('id' => 'CascadeAutocompleteSearchForm','class' => 'quip_search_form'));

	echo $this->Form->input('update_url', array(
					'type' =>'hidden',
					'label' => false,
					'div' => false,
					'value' => $this->Html->url(array_merge(array('controller' => 'devices','action' => 'update'),$this->request->projectvars['VarsArray'])),
				)
	);
	echo $this->Form->end();
?>
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
					type	: "POST",
					url: "<?php echo $this->Html->url(array('action' => 'autocomplete'));?>",
					dataType: "json",
					data: {
						term : request.term,
						field: $(value).attr("id")
						},
					success: function(data) {
						response(data);
						},
					});
				},
				select: function(event,ui) {

					$(value).val(ui.item.value);
				//	$("#CascadeThisId").val(ui.item.key);

					var data = $("#DeviceSearchForm").serializeArray();

					data.push({name: "ajax_true", value: 1});
					data.push({name: "this_id", value: ui.item.key});
					var step = 'update';
					$.ajax({
						type	: "POST",
						cache	: false,
						url		:  $("#DeviceUpdateUrl").val(),
						data	: data,
						success: function(data) {
						//	$("#container").html(data);
					//		$("#container").show();

					//	ChangeFieldValues(step,data);
						}
					});
					return false;
				}
			});
		}
	});
	// Autocomplete JS Ende
});
</script>
