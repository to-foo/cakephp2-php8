<div class="modalarea examiners index inhalt">
	<h2><?php echo __('Search examiner');?></h2>
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

<?php echo $this->Form->create('Examiner', array('class' => 'dialogform')); ?>
<fieldset>
	<?php
                if(isset($selects)) {
                foreach ($selects as $_selects => $_value) {
                echo $this->Form->input('Examiner.'.$_selects,array(
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
		//echo $this->ViewData->EditModulData($this->request->data,$settings,$locale,array('testingcomp_id'=>$testingcomps,'Testingmethod'=>$testingmethods),'Examiner');
                echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Examiner');

        ?>
        </fieldset>


        <fieldset class="slider">
		<?php
            $options = array(
            '1' => __('next certification in') .':'. ' (' . __('Days') . ')',
            '2' => __('next certification in') .':'. ' (' . __('Weeks') . ')',
            '3' => __('next certification in') .':'. ' (' . __('Months') . ')',
            '4' => __('next certification in') .':' . ' (' . __('Years') . ')',
			);



            echo $this->Form->input('ExaminerCertificateData.period',array(
				'label' => false,
				'div' => false,
//				'multiple' => 'multiple',
				'options' => $options,


				)
			);

		echo '<div id="slider-range-max" class="">   <div id="custom-handle" class="ui-slider-handle"></div></div>';

		echo $this->Form->input('Monitorings.OnOff', array(
			'options' => array('0' => __('-',true),'1' => __('Search by eyechecks',true),'2' => __('Search by certificate',true)),
			//'value' => ,
			'type' => 'radio',
			'legend' => false,
			'id' => 'MonitoringsOnOff',
			//'name' => 'data[Monitorings][OnOff]',
			)
		);

		echo '<div class="clear">';

		$checkedMonitorings = 0;
		if(!isset($MonitoringKind)) $MonitoringKind = array();


		echo '</div>';
 ?>
         </fieldset>
<?php
if(Configure::check('ExaminersMaxSearchResult')){
	$ExaminersMaxSearchResult = Configure::read('ExaminersMaxSearchResult');
} else {
	$ExaminersMaxSearchResult = 500;
}

	if(isset($countresult)) {
    	if($countresult > $ExaminersMaxSearchResult){
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

<?php if(Configure::check('ExaminerManagerSearchAutocomplete') && Configure::read('ExaminerManagerSearchAutocomplete') == true) echo $this->element('autocomplete_js_examiner');?>

<script type="text/javascript">

  $( function() {

    <?php     $value = !empty($this->request->data['ExaminerCertificateData'] ['next_certification_in']) ? $this->request->data['ExaminerCertificateData'] ['next_certification_in'] : 0  ;echo 'var handle = $( "#custom-handle" );
    $( "#slider-range-max" ).slider({
      range: "max",
      min: 0,
      max: 30,
      value:'.$value.',
	  stop: function( event, ui ) {
		$("#ExaminerCertificateDataNextCertificationIn").change();
	  },
      create: function() {
        handle.text( $( this ).slider( "value" ) );
      },
      slide: function( event, ui ) {
        $( "#ExaminerCertificateDataNextCertificationIn" ).focus();
        $( "#ExaminerCertificateDataNextCertificationIn" ).val( ui.value );
                handle.text( ui.value );
      }

    });
'?>
  });

  </script>

<script type="text/javascript">
$(document).ready(function(){
<?php
echo '

	$("#ExaminerCertificateDataNextCertificationIn").parent("div").hide();

	var lastdata = $("#ExaminerSearchForm").serializeArray();
	$("#resetsearch").click(function() {
		$("#dialog").load($(this).attr("href"),{"ajax_true": 1,"type": "examiners", "delsession" : 1})
		return false;
	});

	var searching = 0;

	$("#ExaminerSearchForm input, #ExaminerSearchForm select").change(function() {

		if($(this).attr("name") == "data[Monitorings][OnOff]"){
			if($(this).val() == 1){
				$("fieldset.slider div.clear div.checkbox input").prop("checked",false);
				$("fieldset.slider div.clear div.checkbox label").removeClass("ui-state-active");
			} else {
				$("fieldset.slider div.clear div.checkbox input").prop("checked",true);
				$("fieldset.slider div.clear div.checkbox label").addClass("ui-state-active");
			}
			if($("#ExaminerCertificateDataNextCertificationIn").val() == 0){
				return false;
			}
		}

		var data = $("#ExaminerSearchForm").serializeArray();

		searching = 1;
		monitoringkind = 0;

		$.each(data, function( index, field ) {

			if(field.name != "_method" && field.name != "data[type]"){
				// unnötige Leerzeichen entfernen
				data[index].value = $.trim(data[index].value);
				field.value = $.trim(field.value);
				thislength = field.value.length;
			}

			if (field.name == "data[Monitorings][OnOff]"){
				if($("#ExaminerCertificateDataNextCertificationIn").val() == 0) data[index].value = 0;
				monitoringkind = field.value;
			}

		});

		if($(this).attr("id") == "ExaminerCertificateDataNextCertificationIn"){
			if($(this).val() > 0 && monitoringkind == 0) return false;
		}

		if($(this).attr("id") == "ExaminerCertificateDataPeriod"){
			if($("#ExaminerCertificateDataNextCertificationIn").val() < 1){
				return false;
			}
		}

  		$("#ExaminerSearchForm").prepend("<div id=\"waiting\"><span>'.__('Please wait').'</span></div>");
		$("#ExaminerSearchForm div.submit input").val("'.__('The database is searched',true).'");

		data.push({name: "ajax_true", value: 1});
		data.push({name: "shownoresults", value: 1});
		data.push({name: "delsession", value: 1});
		data.push({name: "searching", value: searching});

		$("#ExaminerSearchForm ").find("input, select").prop("disabled", true);

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
$form = '#ExaminerSearchForm';
echo $this->JqueryScripte->SessionFormData($form);
echo $this->JqueryScripte->ModalFunctions();
