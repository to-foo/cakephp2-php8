<?php
if(Configure::check('WebserviceImport') == false) return;
if(Configure::read('WebserviceImport') != true) return;

if(!isset($controller)) return;
if(!isset($action)) return;
if(!isset($minLength)) return;
?>
<div class="quicksearch ">
<?php
//AUFTRAGSNUMMER BEISPIELSWEISE AN WEBSERVICE SCHICKEN UND AUFTRAGSDATEN IMORTIEREN (XML NÖTIG!)
$targetcontroller = 'soapservices';
$targetation = $action;

echo $this->Form->create('QuicksearchWebservice',array('id' => 'QuicksearchWebserviceForm','class' => 'quip_search_form'));

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
	'class' =>'autocomplet searching_autocomplet',
	'label' => false,
	'div' => false,
	'title' => $discription,
	'placeholder' => $placeholder,
	'formaction' => 'autocomplete'
)
);

echo $this->Form->input('json_url', array(
	'type' =>'hidden',
	'label' => false,
	'div' => false,
	'value' => $this->Html->url(array_merge(array('controller' => 'soapservices','action' => $action))),
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
	'value' => $this->Html->url(array_merge(array('controller' => $targetcontroller,'action' => $targetation))),
)
);

echo $this->Form->end();
?>
</div>
<script>
function ChangeFields(data){

	$.each(data,function(key,value) {
		$("#"+key).val("");
		$("#"+key).val(value);

		$("#"+key).trigger($.Event('change'));
		$("#"+key).prop( "disabled", true );


	});


}

$(function() {

	$("form#QuicksearchWebserviceForm").on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});


	$("#QuicksearchWebserviceForm input.autocomplet").each(function(key,value){

		$(this).autocomplete({
			minLength: $("#QuicksearchWebserviceMinLength").val(),
			delay: 4,
			source: function(request,response) {
				$.ajax({
					type	: 'POST',
					url: $("#QuicksearchWebserviceJsonUrl").val(),
					data: {
						ajax_true: 1,
						term : request.term,
						targetcontroller : $("#QuicksearchWebserviceTargetController").val(),
						targetation : $("#QuicksearchWebserviceTargetAction").val(),
						reportnumber: <?php echo $this->request->projectvars['VarsArray'][4];?>
					},
					dataType: 'json',

					success: function(data) {
						if(data.length == 0){
							alert('Keine Daten vorhanden!')
						}else{
							ChangeFields(data);
						}
						//	response(data);
					},
				});
			}
		});

	});



});


</script>
