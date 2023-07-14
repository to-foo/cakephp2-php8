<?php
if(!isset($action)) return;
if(!isset($minLength)) return;
if(!isset($discription)) return;

$targetcontroller = 'reportnumbers';
$targetation = 'view';

echo $this->Form->create('Cascade',array('id' => 'CascadeSearchForm','class' => 'quip_search_form'));

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
			echo $this->Form->input('update_url', array(
							'type' =>'hidden',
							'label' => false,
							'div' => false,
							'value' => $this->Html->url(array_merge(array('controller' => 'cascades','action' => 'update'),$this->request->projectvars['VarsArray'])),
						)
					);
echo $this->Form->input('json_url', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $this->Html->url(array_merge(array('controller' => 'cascades','action' => $action),$this->request->projectvars['VarsArray'])),
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
function ChangeFieldValues (step,data) {

var datas = data;

		if(step == 'update') {
datav = JSON.parse(datas);
//alert (datav.result);
			$.each(datav.result, function(key,value) {

				$.each(datav.fieldids, function(fkey,fvalue) {

			//	var elem = document.getElementById('TechnicalplaceLocationFrom');
					if($('#'+ fkey).length > 0 && key == fvalue){
						$('#'+ fkey).val("");
						$('#'+ fkey).val(value);

				}
			});
			});
		}
}

$(function() {

	$("form#CascadeSearchForm").on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	var i = 0;
	$("#CascadeSearchForm input.autocompletion").each(function(key,value){

		$(this).autocomplete({
			minLength: $("#CascadeMinLength").val(),
			delay: 4,
			source: function(request,response) {
				$.ajax({
					type	: "POST",
					url: $("#CascadeJsonUrl").val(),
					dataType: "json",
					data: {
						term : request.term,
						targetcontroller : $("#CascadeTargetController").val(),
						targetation : $("#CascadeTargetAction").val(),
					},

			success: function(data) {
				response(data);
				},
			});
			},
			select: function(event,ui) {

				$("#CascadeSearchingAutocomplet").val(ui.item.value);
				$("#CascadeThisId").val(ui.item.key);

				var data = $("#CascadeSearchForm").serializeArray();

				data.push({name: "ajax_true", value: 1});
				data.push({name: "this_id", value: ui.item.key});
			 	var step = 'update';
				$.ajax({
					type	: "POST",
					cache	: false,
					url		:  $("#CascadeUpdateUrl").val(),
					data	: data,
					success: function(data) {
					//	$("#container").html(data);
				//		$("#container").show();

					ChangeFieldValues(step,data);
					}
				});
				return false;
			}
		});
		i++;
	});

	$("#CascadeSearchingAutocomplet").change(function() {
		if($("#CascadeSearchingAutocomplet").val() == "" && $("#CascadeThisId").val() == 0){
			$("#container").load($("#CascadeSearchForm").attr("action"), {"ajax_true": 1});
		}
	});

	$("form#CascadeSearchForm").bind("submit", function() {
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
