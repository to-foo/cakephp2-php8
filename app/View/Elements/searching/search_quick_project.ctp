<?php
if(!isset($action)) return;
if(!isset($minLength)) return;
if(!isset($discription)) return;

echo $this->Form->create('Quicksearch',array('id' => 'QuicksearchForm','class' => 'quip_search_form'));
echo $this->Form->input('hidden', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 1,
				)
			);

echo $this->Form->input('this_id', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 0,
				)
			);

echo $this->Form->input('searching_autocomplet', array(
				'class' =>'autocompletion searching_autocomplet',
				'label' => false,
				'div' => false,
				'title' => $discription,
				'placeholder' => $discription,
				'formaction' => 'autocomplete'
				)
			);

echo $this->Form->input('json_url', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $this->Html->url(array_merge(array('action' => $action),$this->request->projectvars['VarsArray'])),
			)
		);

		echo $this->Form->input('min_length', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => $minLength,
					)
				);

echo $this->Form->end();
?>
<script>
$(function() {

	$("form#QuicksearchForm").on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	var i = 0;
	$("#QuicksearchForm input.autocompletion").each(function(key,value){

		$(this).autocomplete({
			minLength: $("#QuicksearchMinLenght").val(),
			delay: 4,
			source: function(request,response) {
				$.ajax({
					type	: "POST",
					cache	: false,
					url: $("#QuicksearchJsonUrl").val(),
					dataType: "json",
					data: {
						term : request.term,
					},

			success: function(data) {
				response(data);
				},
			});
			},
			select: function(event,ui) {

				$("#QuicksearchSearchingAutocomplet").val(ui.item.value);
				$("#QuicksearchThisId").val(ui.item.key);

				var data = $("#QuicksearchForm").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "this_id", value: ui.item.key});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: $("#QuicksearchForm").attr("action"),
					data	: data,
					success: function(data) {
						$("#container").html(data);
						$("#container").show();
					}
				});
				return false;
			}
		});
		i++;
	});

	$("#QuicksearchSearchingAutocomplet").change(function() {
		if($("#QuicksearchSearchingAutocomplet").val() == "" && $("#QuicksearchThisId").val() == 0){
			$("#container").load($("#QuicksearchForm").attr("action"), {"ajax_true": 1});
		}
	});

	$("form#QuicksearchForm").bind("submit", function() {
		if($("#this_id").val() == 0){
			return false;
		}

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: this.getAttribute("action"),
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
			}
		});
		return false;
	});
});
</script>
