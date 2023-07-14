<?php
if(!isset($action)) return;
if(!isset($minLength)) return;
if(!isset($discription)) return;

$targetcontroller = 'reportnumbers';
$targetation = 'index';

echo $this->Form->create('QuicksearchOrder',array('id' => 'QuicksearchOrderForm','class' => 'quip_search_form'));

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
				'value' => $this->Html->url(array_merge(array('controller' => 'topprojects','action' => $action),$this->request->projectvars['VarsArray'])),
			)
		);

echo $this->Form->input('min_length', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $minLength,
			)
		);

echo $this->Form->input('target_controller', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $targetcontroller,
			)
		);

echo $this->Form->input('target_action', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $targetation,
			)
		);

		echo $this->Form->input('target_order', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => $this->Html->url(array_merge(array('controller' => $targetcontroller,'action' => $targetation), $this->request->projectvars['VarsArray'])),
					)
				);

echo $this->Form->end();
?>
<script>
$(function() {

	$("form#QuicksearchOrderForm").on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	var i = 0;
	$("#QuicksearchOrderForm input.autocompletion").each(function(key,value){

		$(this).autocomplete({
			minLength: $("#QuicksearchOrderMinLength").val(),
			delay: 4,
			source: function(request,response) {
				$.ajax({
					type	: "POST",
					url: $("#QuicksearchOrderJsonUrl").val(),
					dataType: "json",
					data: {
						term : request.term,
						targetcontroller : $("#QuicksearchOrderTargetController").val(),
						targetation : $("#QuicksearchOrderTargetAction").val(),
					},

			success: function(data) {
				response(data);
				},
			});
			},
			select: function(event,ui) {

				$("#QuicksearchOrderSearchingAutocomplet").val(ui.item.value);
				$("#QuicksearchOrderThisId").val(ui.item.key);

				var data = $("#QuicksearchOrderForm").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "this_id", value: ui.item.key});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: ui.item.key,
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

	$("#QuicksearchOrderSearchingAutocomplet").change(function() {
		if($("#QuicksearchOrderSearchingAutocomplet").val() == "" && $("#QuicksearchOrderThisId").val() == 0){
			$("#container").load($("#QuicksearchOrderForm").attr("action"), {"ajax_true": 1});
		}
	});

	$("form#QuicksearchOrderForm").bind("submit", function() {
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
