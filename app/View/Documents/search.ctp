<div class="modalarea examiners index inhalt">
	<h2><?php echo __('search document');?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
/*if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
	} */
?>

<?php echo $this->Form->create('Document', array('class' => 'dialogform')); ?>
<fieldset>
	<?php
                if(isset($selects)) {
                foreach ($selects as $_selects => $_value) {
                echo $this->Form->input('Document.'.$_selects,array(
				'label' => $_value['discription'],
//				'multiple' => 'multiple',
				'empty' => __('choose one',true),
				'options' => $_value['values'],
				)
			);

                }
            }

        ?>
  </fieldset>

        <fieldset>
        <?php
		echo $this->ViewData->EditModulData($this->request->data,$settings,$locale,array('testingcomp_id'=>$testingcomps),'Document');


        ?>
        </fieldset>



<?php
if(Configure::check('DevicesMaxSearchResult')){
	$DevicesMaxSearchResult = Configure::read('DevicesMaxSearchResult');
} else {
	$DevicesMaxSearchResult = 500;
}

	if(isset($countresult)) {
    	if($countresult > $DevicesMaxSearchResult){
		$options = array(
 	   		'label' => __('Too many results found', true),
 	   		'disabled' => 'disabled',
		);
    }


    else if( $countresult == 0){
        $options = array(
 	   'label' => __('No matching results found', true),
 	   'disabled' => 'disabled',
	);
    }else { $options = $countresult. ' ' .  __('Show matching results', true);}
    if (isset($countresult) ) {
        echo $this->Html->link(__('Zurücksetzen'),array('action'=>'search'),array('id'=>'resetsearch', 'class' => 'resetsearch round'));
        echo $this->Form->end($options);
    }
}


?>


</div>

<?php if(Configure::check('DocumentManagerSearchAutocomplete') && Configure::read('DocumentManagerSearchAutocomplete') == true) echo $this->element('autocomplete_js_document');?>



<script type="text/javascript">
$(document).ready(function(){
<?php
echo '



	var lastdata = $("#DocumentSearchForm").serializeArray();
	$("#resetsearch").click(function() {
		$("#dialog").load($(this).attr("href"),{"ajax_true": 1,"type": "devices", "delsession" : 1})
		return false;
	});

	var searching = 0;

	$("#DocumentSearchForm input, #DocumentSearchForm select").change(function() {



		var data = $("#DocumentSearchForm").serializeArray();

		searching = 1;


		$.each(data, function( index, field ) {

			if(field.name != "_method" && field.name != "data[type]"){
				// unnötige Leerzeichen entfernen
				data[index].value = $.trim(data[index].value);
				field.value = $.trim(field.value);
				thislength = field.value.length;
			}



		});



  		$("#DocumentSearchForm").prepend("<div id=\"waiting\"><span>'.__('Please wait').'</span></div>");
		$("#DocumentSearchForm div.submit input").val("'.__('The database is searched',true).'");

		data.push({name: "ajax_true", value: 1});
		data.push({name: "shownoresults", value: 1});
		data.push({name: "delsession", value: 1});
		data.push({name: "searching", value: searching});

		$("#DocumentSearchForm ").find("input, select").prop("disabled", true);

		$.ajax({
			type	: "POST",
			cache	: true,
			url	: "'.Router::url(array_merge(array('action'=>'search'), $VarsArray)).'",
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").show();
			}
		});
		return false;
	});'
?>
    });
 </script>
<?php

echo $this->JqueryScripte->ModalFunctions();
