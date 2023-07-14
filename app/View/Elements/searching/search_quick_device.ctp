<?php
if(!isset($action)) return;
if(!isset($minLength)) return;
if(!isset($target_id)) return;
if(!isset($targedaction)) return;
if(!isset($discription)) return;

$FormName = 'QuicksearchExaminer';
$FormId = 'QuicksearchExaminerForm';

$ResultParms = array_fill(0, 16, 0);

echo $this->Form->create($FormName,array('id' => $FormId,'class' => 'quip_search_form'));

echo $this->Form->input('hidden', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 1,
				)
			);

echo $this->Form->input('this_form_id', array(
				'type' =>'hidden',
				'id' => 'FormId',
				'label' => false,
				'div' => false,
				'value' => $FormId,
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
				'id' => 'JsonUrl',
				'label' => false,
				'div' => false,
				'value' => $this->Html->url(array_merge(array('controller' => 'devices','action' => $action),$this->request->projectvars['VarsArray'])),
			)
		);

echo $this->Form->input('min_length', array(
				'type' =>'hidden',
				'id' => 'MinLength',
				'label' => false,
				'div' => false,
				'value' => $minLength,
			)
		);

echo $this->Form->input('target_controller', array(
				'type' =>'hidden',
				'id' => 'TargetController',
				'label' => false,
				'div' => false,
				'value' => 'devices',
			)
		);

echo $this->Form->input('target_action', array(
				'type' =>'hidden',
				'id' => 'TargetAction',
				'label' => false,
				'div' => false,
				'value' => $targedaction,
			)
		);

		echo $this->Form->input('target_result', array(
						'type' =>'hidden',
						'id' => 'TargetResult',
						'label' => false,
						'div' => false,
						'value' => $this->Html->url(array_merge(array('controller' => 'devices','action' => $targedaction),$ResultParms)),
					)
				);

echo $this->Form->end();
?>
<script>
$(function() {

	var form_id = $("#FormId").val();
	var min_lenght = $("#MinLength").val();
	var target_controller = $("#TargetController").val();
	var json_url = $("#JsonUrl").val();
	var target_action = $("#TargetAction").val();
	var target_result = $("#TargetResult").val();

	$("form#" + form_id).on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	var i = 0;

	$("#" + form_id + " input.autocompletion").each(function(key,value){

		$(this).autocomplete({
			minLength: min_lenght,
			delay: 4,
			source: function(request,response) {
				$.ajax({
					type	: "POST",
					url: json_url,
					dataType: "json",
					data: {
						term : request.term,
						targetcontroller : target_controller,
						targedaction : target_action,
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
				data.push({name: "device_id", value: ui.item.key});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: target_result + "/" + ui.item.key,
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
