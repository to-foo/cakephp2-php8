<?php
if(!isset($action)) return;
if(!isset($minLength)) return;
if(!isset($target_id)) return;
if(!isset($targedaction)) return;
if(!isset($discription)) return;

$FormName = 'QuicksearchIncommingGoodInspection';
$FormId = 'QuicksearchExaminerForm';

$ResultParms = array_fill(0, 17, 0);

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

echo $this->Form->input('searching_order_no', array(
				'class' =>'autocompletion searching_autocomplet',
				'label' => false,
				'div' => false,
				'title' => 'Bestellnummer',
				'placeholder' => 'Bestellnummer',
				'formaction' => 'autocomplete'
				)
);
echo $this->Form->input('searching_material', array(
				'class' =>'autocompletion searching_autocomplet',
				'label' => false,
				'div' => false,
				'title' => 'Material-Id',
				'placeholder' => 'Material-Id',
				'formaction' => 'autocomplete'
				)
);
echo $this->Form->input('searching_charge', array(
				'class' =>'autocompletion searching_autocomplet',
				'label' => false,
				'div' => false,
				'title' => 'Charge',
				'placeholder' => 'Charge',
				'formaction' => 'autocomplete'
				)
);
echo $this->Form->input('searching_technical_place', array(
				'class' =>'autocompletion searching_autocomplet',
				'label' => false,
				'div' => false,
				'title' => 'Technischer Platz',
				'placeholder' => 'Technischer Platz',
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

echo $this->Form->input('json_data', array(
				'type' =>'hidden',
				'id' => 'JsonData',
				'label' => false,
				'div' => false,
				'value' => json_encode($JsonAutocomplete),
			)
	);
	echo $this->Form->input('json_table', array(
					'type' =>'hidden',
					'id' => 'JsonTable',
					'label' => false,
					'div' => false,
					'value' => json_encode($JsonAutocomplete),
				)
		);


?>
<script>
$(function() {

	form_id = $("#FormId").val();
	min_lenght = $("#MinLength").val();
	json_url = $("#JsonUrl").val();
	target_controller = $("#TargetController").val();
	target_action = $("#TargetAction").val();
	target_result = $("#TargetResult").val();

	var json_data = JSON.parse($("#JsonData").val());
	var json_table = JSON.parse($("#JsonTable").val());

	$("form#QuicksearchIncommingGoodInspection").on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	var i = 0;

	$("#" + form_id + " input.autocompletion").each(function(key,value){

		var this_id = $(this).attr("id");

		console.log(json_table[this_id]);


		$(this).autocomplete({
			minLength: min_lenght,
			delay: 4,
			source: json_data[this_id],
			select: function(event,ui) {
			}
		});

		i++;

	});

});
</script>
