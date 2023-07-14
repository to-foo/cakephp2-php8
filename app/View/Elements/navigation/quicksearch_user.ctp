<?php
$action = 'quicksearch';
$showsummary = false;
$ControllerArray = $ControllerQuickSearch;
$description = $ControllerArray['description'];
$minLength = $ControllerArray['minLength'];
$targetcontroller = $ControllerArray['targetcontroller'];
$target_id = $ControllerArray['target_id'];
$targetation = $ControllerArray['targetation'];
$Quicksearch = $ControllerArray['Quicksearch'];
$QuicksearchForm = $ControllerArray['QuicksearchForm'];
$QuicksearchSearchingAutocomplet = $ControllerArray['QuicksearchSearchingAutocomplet'];
$Model = $ControllerArray['Model'];
$Field = $ControllerArray['Field'];

$autourl = $this->Html->url(array_merge(array('controller'=>$targetcontroller,'action'=>$action),$this->request->projectvars['VarsArray']));
$url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>$targetation));
$clear_url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>'index'));
$summery_url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>'summary'));

echo $this->Form->input('QuicksearchForm', array('type' =>'hidden','value' => $QuicksearchForm));
echo $this->Form->input('QuicksearchSearchingAutocomplet', array('type' =>'hidden','value' => $QuicksearchSearchingAutocomplet));
echo $this->Form->input('minLength', array('type' =>'hidden','value' => $minLength));
echo $this->Form->input('targetcontroller', array('type' =>'hidden','value' => $targetcontroller));
echo $this->Form->input('targetation', array('type' =>'hidden','value' => $targetcontroller));
echo $this->Form->input('ControllerArrayModel', array('type' =>'hidden','value' => $ControllerArray['Model']));
echo $this->Form->input('ControllerArrayField', array('type' =>'hidden','value' => $ControllerArray['Field']));
echo $this->Form->input('autourl', array('type' =>'hidden','value' => $autourl));
echo $this->Form->input('url', array('type' =>'hidden','value' => $url));

echo $this->Form->create($Quicksearch,array('id' => $QuicksearchForm,'class' => 'quip_search_form'));

echo $this->Form->input('searching_autocomplet', array('class' =>'autocompletion searching_autocomplet','label' => false,'div' => false,'title' => $description,'placeholder' => $description,'formaction' => 'autocomplete'));

echo$this->Form->end();

?>

<script>
$(function() {

$("#QuickUsersearchSearchingAutocomplet").on("keyup keypress", function(e) {

	var keyCode = e.keyCode || e.which;

	if(keyCode === 13) {
		e.preventDefault();
		return false;
	}

});

$("#QuickUsersearchSearchingAutocomplet").autocomplete({

	minLength: $("#minLength").val(),
	delay: 4,

	source: function(request,response) {

		var data = new Array;

		data.push({name: "term", value: request.term});
		data.push({name: "targetcontroller", value: $("#targetcontroller").val()});
		data.push({name: "targetation", value: $("#targetation").val()});
		data.push({name: "model", value: $("#ControllerArrayModel").val()});
		data.push({name: "field", value: $("#ControllerArrayField").val()});

		$.ajax({

			url: $("#autourl").val(),
			dataType: "json",
			data: data,
			success: function(data) {
				response(data);
			},
		});
	},
	select: function(event,ui) {

		$("#QuickUsersearchSearchingAutocomplet").val(ui.item.value);
		var data = $("#QuickUsersearchSearchingAutocomplet").serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "id", value: ui.item.key});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#url").val(),
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").show();
				}
			});

		return false;

		}

	});

});
</script>
