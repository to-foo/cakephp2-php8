<script>
	var datedata = new Array();
</script>

<div class="quicksearch">
<?php // if($ShowOrderQuickSearch > 0)echo $this->Navigation->quickOrderSearching('quickreportsearch',2,__('Order', true));?>
<?php //echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
</div>
<div class="users index inhalt">
<h2><?php if(isset($h2)) echo $h2; ?></h2>
<?php //echo $this->Html->link(__('load search data'), array_merge(array('action' => 'insertdata'),$this->request->projectvars['VarsArray']),array('class' => 'ajax'));?>
<?php echo $this->Form->create('Generally',array('class'=>'search_form')); ?>
<fieldset>
<?php
$Lang = Configure::read('Config.language');
$Dateformat = Configure::read('Dateformat');

$Language = "en";
$Date = "Y-m-d";
$Datetime = "Y-m-d H:i";
$Time = "H:i:s";

if($Lang == "deu") $Language = "de";
if($Lang == "eng") $Language = "en";

if(isset($Dateformat[$Lang]['date'])) $Date = $Dateformat[$Lang]['date'];
if(isset($Dateformat[$Lang]['datetime'])) $Datetime = $Dateformat[$Lang]['datetime'];
if(isset($Dateformat[$Lang]['time'])) $Time = $Dateformat[$Lang]['time'];

echo $this->Form->input('LanguageForPicker',array('type' => 'hidden','value' => $Language));
echo $this->Form->input('DateForPicker',array('type' => 'hidden','value' => $Date));
echo $this->Form->input('DateTimeForPicker',array('type' => 'hidden','value' => $Datetime));
echo $this->Form->input('TimeForPicker',array('type' => 'hidden','value' => $Time));

if(isset($SearchFieldsStandard)){
	foreach($SearchFieldsStandard->fields->children() as $_key => $_fields){

		$fieldtype = trim($_fields->fieldtype);
		$Model = trim($_fields->model);
		$Field = Inflector::camelize(trim($_fields->option));
		$field = trim($_fields->option);
		$local = trim($_fields->description->$locale);
		$id = $Model . $Field;

		if(isset($_fields['disabled']) && $_fields['disabled'] == 'disabled') $IsDisabled = array('disabled' => 'disabled');
		else $IsDisabled = null;

		switch($fieldtype){
			case 'autocomplete':
			echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
				'class' => 'autocompletion',
				'label' => $local,
				'id' => $Model.$Field,
				'name' => 'data['.$Model.']['.$field.']',
			)
		);
		break;
		case 'dropdown':
		if(!isset($this->request->data[$Model])) break;
		if(!isset($this->request->data[$Model][$Field])) break;

		if(is_countable($this->request->data[$Model][$Field]['value']) && count($this->request->data[$Model][$Field]['value']) == 1 && isset($this->request->data[$Model][$Field]['value'][0]) && empty($this->request->data[$Model][$Field]['value'][0])) break;

		echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
			'class' => 'dropdown',
			'label' => $local,
			'id' => $Model.$Field,
			'name' => 'data['.$Model.']['.$field.']',
			'options' => $this->request->data[$Model][$Field]['value'],
			'selected' => $this->request->data[$Model][$Field]['selected'],
			$IsDisabled
		)
	);
	break;
	case 'multiselect':
	echo '<fieldset class="multiple_field">';
	echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
		'class' => 'multiselect',
		'multiple' => 'multiple',
		'label' => $local,
		'id' => $Model.$Field,
		'name' => 'data['.$Model.']['.$field.']',
		'options' => $this->request->data[$Model][$Field]['value'],
		'selected' => $this->request->data[$Model][$Field]['selected'],
		$IsDisabled
		)
		);
		echo '</fieldset>';

		break;
		case 'date':
		if(!isset($this->request->data[$Model][$Field]['end']) && !isset($this->request->data[$Model][$Field]['start'])) break;
		echo $this->Form->input($Model.$Field.'StartTimestamp',array('id' => $Model.$Field.'StartTimestamp','name' => 'data['.$Model.']['.$field.'][start_timestamp]','type' => 'hidden','value' => $this->request->data[$Model][$Field]['start_timestamp']));
		echo $this->Form->input($Model.$Field.'EndTimestamp',array('id' => $Model.$Field.'EndTimestamp','name' => 'data['.$Model.']['.$field.'][end_timestamp]','type' => 'hidden','value' => $this->request->data[$Model][$Field]['end_timestamp']));
		echo '<div class="input text">';
			echo '<label for="'.$Model.$Field.'Start">'.$this->request->data[$Model][$Field]['description'].'</label>';
			echo $this->Form->input($this->request->data[$Model][$Field]['description'] . ' (' . __('from',true) . ')',array(
			'class' => 'date',
			'id' => $Model.$Field.'Start',
			'name' => 'data['.$Model.']['.$field.'][start]',
			'placeholder' => __('from',true),
			'div' => false,
			'label' => false,
			)
			);
			echo $this->Form->input($this->request->data[$Model][$Field]['description'] . ' (' . __('to',true) . ')',array(
			'class' => 'date',
			'id' => $Model.$Field.'End',
			'name' => 'data['.$Model.']['.$field.'][end]',
			'placeholder' => __('to',true),
			'div' => false,
			'label' => false,
			)
			);
			echo '</div>';
			/*
			echo '
			<script>
			$(document).ready(function(){
			$("#'.$Model.$Field.'Start, #'.$Model.$Field.'End").prop({"readOnly": false}).datetimepicker({
			timepicker:	false,
			format:			$("#DateForPicker").val(),
			lang:				$("#GenerallyLanguageForPicker").val(),
		});
	});
</script>
';
*/
break;
}
}

}
?>
</fieldset>
<fieldset>
<?php
if(isset($SearchFieldsAdditional)) {
	foreach($SearchFieldsAdditional->fields->children() as $_key => $_fields){

		$fieldtype = trim($_fields->fieldtype);
		$Model = trim($_fields->model);
		$Field = Inflector::camelize(trim($_fields->option));
		$field = trim($_fields->option);
		$local = trim($_fields->description->$locale);
		$id = $Model . $Field;

		switch($fieldtype){
			case 'dropdown':
			if(isset($this->request->data[$Model][$Field]['value'])) {
				if(is_countable($this->request->data[$Model][$Field]['value']) && count($this->request->data[$Model][$Field]['value']) == 1 && empty($this->request->data[$Model][$Field]['value'][0])) break;
					echo $this->Form->input(trim($_fields->option),array(
						'class' => 'dropdown',
						'label' => $local,
						'id' => $Model.$Field,
						'name' => 'data['.$Model.']['.$field.']',
						'options' => $this->request->data[$Model][$Field]['value']
					)
				);
			}

		break;

		case 'multiselect':
		echo '<fieldset class="multiple_field">';
		if(isset($this->request->data[$Model][$Field]['value'])) {
			if(is_countable($this->request->data[$Model][$Field]['value']) && count($this->request->data[$Model][$Field]['value']) == 1 && empty($this->request->data[$Model][$Field]['value'][0])) break;
			echo $this->Form->input(trim($_fields->option),array(
				'class' => 'multiselect',
				'multiple' => 'multiple',
				'label' => $local,
				'id' => $Model.$Field,
				'name' => 'data['.$Model.']['.$field.']',
				'options' => $this->request->data[$Model][$Field]['value']
				)
			);
		}
	echo '</fieldset>';
	break;

	case 'autocomplete':
	echo $this->Form->input(trim($_fields->option),array(
		'class' => 'autocompletion',
		'label' => $local,
		'id' => $Model.$Field,
		'name' => 'data['.$Model.']['.$field.']',
	)
);
break;

case 'date':
if(!isset($this->request->data[$Model][$Field]['end'][0]) && !isset($this->request->data[$Model][$Field]['start'][0])) break;

echo '<div class="input text">';
	echo '<label for="'.$Model.$Field.'Start">'.$local.'</label>';
	echo $this->Form->input($this->request->data[$Model][$Field]['description'] . ' (' . __('from',true) . ')',array(
	'class' => 'date',
	'id' => $Model.$Field.'Start',
	'name' => 'data['.$Model.']['.$field.'][start]',
	'placeholder' => __('from',true),
	'div' => false,
	'label' => false,
	'value' => $this->request->data[$Model][$Field]['start'][0]
	)
	);
	echo $this->Form->input($this->request->data[$Model][$Field]['description'] . ' (' . __('to',true) . ')',array(
	'class' => 'date',
	'id' => $Model.$Field.'End',
	'name' => 'data['.$Model.']['.$field.'][end]',
	'placeholder' => __('to',true),
	'div' => false,
	'label' => false,
	'value' => $this->request->data[$Model][$Field]['end'][0]
	)
	);
	echo '</div>';
	/*
	echo '
	<script>
		$(document).ready(function(){
			$("#'.$Model.$Field.'Start, #'.$Model.$Field.'End").prop({"readOnly": false}).datetimepicker({
				timepicker:	false,
				format:			$("#GenerallyDateForPicker").val(),
				lang:				$("#GenerallyLanguageForPicker").val(),
			});
		});
	</script>
	';
	*/
	break;
}
}

}
?>
</fieldset>
<?php echo $this->Form->button(__('Search testing reports',true),array('type' => 'button','id' => 'SendThisReportForm')); ?>
<?php //echo $this->Form->button(__('Search orders',true),array('type' => 'button','id' => 'SendThisOrderForm')); ?>
<?php //echo $this->Form->button(__('Show statistic',true),array('type' => 'button','id' => 'SendThisStatistikForm')); ?>
<?php echo $this->Form->button(__('Reset',true),array('type' => 'button','id' => 'ResetThisForm')); ?>
<?php // echo '<button type="button" id="testtesttest">Drück mich</button> <button type="button" id="Blabla">Wechsle mich</button> <button type="button" id="TestMultible">TestMultible</button>';?>
<?php echo $this->Form->end(); ?>
</div>
<div class="clear" id="testdiv"></div>
<div class="update_field_data" id="update_field_data"></div>
<?php
$autourl = $this->Html->url(array_merge(array('controller' => 'searchings','action' => 'auto'),$this->request->projectvars['VarsArray']));
echo $this->element('js/searchresult_link_close');
if(count($HistorySearch) > 0) echo $this->element('search_history_window', array('HistorySearch' => $HistorySearch));


?>
<script type="text/javascript">
$(document).ready(function(){

	var i = 0;
	var elementwidth = new Array();
	var searchdata = new Array();
	var formdata = new Array();
	var data = new Array();
	var searchdatastart = new Array();
	var testtesttest = new Array();

	$("form div.search_history select option").hover(function() {
		alert($(this).val());
	});

	function CssDropdownSearchStart() {
		$("#GenerallySearchForm select").css("background-image","url(img/indicator.gif)");
		$("#GenerallySearchForm select").css("background-repeat","no-repeat");
		$("#GenerallySearchForm select, #GenerallySearchForm input").css("background-position","2px 2px");
		$("#GenerallySearchForm select, #GenerallySearchForm input").css("background-size","auto 8px");
	}

	function CssDropdownSearchStop() {
		$("#GenerallySearchForm select").css("background-image","none");
	}

	function DeleteFormElements(searchdata){

		$("#SendThisReportForm").text("<?php echo __('Search testing reports',true);?>");

		$(searchdata).each(function(key,value){
			if($("#" + value.id).hasClass("autocompletion")){$("#" + value.id).val("");}
			if($("#" + value.id).hasClass("dropdown")){$("#" + value.id).val("0");}
		});
	}

 	function ChangeDropdownOptions(data){

		searchdata.length = 0;

		if(data.CountOfSearch > 0){
			$("#SendThisReportForm").text(data.CountOfSearch + " " + "<?php echo __('reports found',true)?>");
			$("#SendThisReportForm").removeAttr("disabled");
			$("#SendThisStatisticForm").removeAttr("disabled");
		} else {
			$("#SendThisReportForm").text("<?php echo __('Non testing reports found',true);?>");
			$("#SendThisReportForm").attr("disabled","disabled");
			$("#SendThisStatisticForm").attr("disabled","disabled");
		}

		if(data.CountOfOrders > 0){
			$("#SendThisOrderForm").text(data.CountOfOrders + " " + "<?php echo __('orders found',true)?>");
			$("#SendThisOrderForm").removeAttr("disabled");
		} else {
			$("#SendThisOrderForm").text("<?php echo __('Non orders found',true);?>");
			$("#SendThisOrderForm").attr("disabled","disabled");
		}

		$.each(data,function(key,value) {

			if(jQuery.type(value) == "object"){

				$.each(value,function(key2,value2) {
					if(key2 == "Created" || key2 == "DateOfTest"){

						idstart = key + key2 + "Start";
						idend = key + key2 + "End";
						namestart = $("#" + key + key2 + "Start").attr("name");
						nameend = $("#" + key + key2 + "End").attr("name");
						valstart = $("#" + key + key2 + "Start").val();
						valend = $("#" + key + key2 + "End").val();

						idstarttimestamp = key + key2 + "StartTimestamp";
						idendtimestamp = key + key2 + "EndTimestamp";
						namestarttimestamp = $("#" + key + key2 + "StartTimestamp").attr("name");
						nameendtimestamp = $("#" + key + key2 + "EndTimestamp").attr("name");
						valstarttimestamp = $("#" + key + key2 + "StartTimestamp").val();
						valendtimestamp = $("#" + key + key2 + "EndTimestamp").val();

						if(value2.selected == undefined){
							startvalue = value2.start;
							endvalue = value2.end;
							startvaluetimestamp = value2.start_timestamp;
							endvaluetimestamp = value2.end_timestamp;
						} else {
							startvalue = value2.selected.start;
							endvalue = value2.selected.end;
							startvaluetimestamp = value2.selected.start_timestamp;
							endvaluetimestamp = value2.selected.end_timestamp;
						}

						$("#" + key + key2 + "Start").val(startvalue);
						$("#" + key + key2 + "End").val(endvalue);

						$("#" + key + key2 + "StartTimestamp").val(startvaluetimestamp);
						$("#" + key + key2 + "EndTimestamp").val(endvaluetimestamp);

						searchdata.push({id: idstart, name: namestart, value: startvalue});
						searchdata.push({id: idend, name: nameend, value: endvalue});

						searchdata.push({id: idstarttimestamp, name: namestarttimestamp, value: startvaluetimestamp});
						searchdata.push({id: idendtimestamp, name: nameendtimestamp, value: endvaluetimestamp});

					}

					if($("#" + key + key2).hasClass("dropdown")){

						thisval = $("#" + key + key2).val();
						thisid = key + key2;
						thisname = $("#" + key + key2).attr("name");

						$("#" + key + key2).find("option").remove();

						if(typeof value2.disabled !== "undefined" && value2.disabled == "disabled"){$("#" + key + key2).attr("disabled","disabled");}
						else {$("#" + key + key2).removeAttr("disabled");}

						$.each(value2.value,function(key3,value3) {
							$.each(value3,function(key4,value4) {
								$("#" + key + key2).append("<option value=\"" + key4 + "\">" + value4 + "</option>");
							});
						});

						$("#" + key + key2 + "option:selected").removeAttr("selected");
						$("#" + key + key2 + " option[value='" + value2.selected + "']").attr('selected',true);

					}

					if($("#" + key + key2).hasClass("dropdown")){

						thisval = $("#" + key + key2).val();
						thisid = key + key2;
						thisname = $("#" + key + key2).attr("name");

						searchdata.push({id: thisid, name: thisname, value: thisval});

					};

					if($("#" + key + key2).hasClass("multiselect")){

						thisval = $("#" + key + key2).val();
						thisid = key + key2;
						thisname = $("#" + key + key2).attr("name");
						element = new Array();

						if(thisval != 0){
							element.push(thisval);
						}

						searchdata.push({id: thisid, name: thisname, value: element});

					};
				});
			}
		});

		$(elementwidth).each(function(key,value){
			if(value.width > 0){
				$("#" + value.id).width(value.width);
			}
		});

		if(data.CountOfOrders == 0){
			$("button#SendThisOrderForm").hide();
		} else {
			$("button#SendThisOrderForm").show();
		}

		if(data.CountOfSearch == 0){
			$("button#SendThisReportForm").hide();
			$("button#SendThisStatisticForm").hide();
		} else {
			$("button#SendThisReportForm").show();
			$("button#SendThisStatisticForm").show();
		}

		CssDropdownSearchStop();

	}

	$("#GenerallySearchForm select,#GenerallySearchForm input").each(function(key,value){
		elementwidth.push({id: $(this).attr("id"), width: $(this).width()});
	});

	$("#GenerallySearchForm select.dropdown").each(function(key,value){
		if($(this).val() > 0){
			searchdata.push({id: $(this).attr("id"), name: $(this).attr("name"), value: $(this).val()});
			formdata.push({id: $(this).attr("id"), name: $(this).attr("name"), value: $(this).val()});
			searchdatastart.push({id: $(this).attr("id"), name: $(this).attr("name"), value: $(this).val()});
		}
	});

	$("#GenerallySearchForm select.multiselect").each(function(key,value){

			element = new Array();

			searchdata.push({id: $(this).attr("id"), name: $(this).attr("name"), value: element});
			formdata.push({id: $(this).attr("id"), name: $(this).attr("name"), value: element});
			searchdatastart.push({id: $(this).attr("id"), name: $(this).attr("name"), value: element});

	});

	if(searchdata.length > 0){

		CssDropdownSearchStart();

		$.ajax({
			type	: "POST",
			data	: searchdata,
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
			dataType: "json",
			success: function(data) {
				ChangeDropdownOptions(data);
			},
		});

	}

	$("#GenerallySearchForm select.dropdown").change(function() {

		CssDropdownSearchStart();

		var name = $(this).attr("name");
		var id = $(this).attr("id");
		var val = $(this).val();

		$(searchdata).each(function(key,value){
			if(value.name == name){
				searchdata.splice(key, 1);
			}
		});

		if(val == 0){
			searchdata.push({id: id, name: name, value: "0"});
		} else {
			searchdata.push({id: id, name: name, value: val});
		}

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
			data	: searchdata,
			dataType: "json",
			success: function(data) {
				ChangeDropdownOptions(data);
			},
		});
	});

	$("#GenerallySearchForm select.multiselect").change(function() {

		CssDropdownSearchStart();

		var name = $(this).attr("name");
		var id = $(this).attr("id");
		var val = $(this).val();

		$(searchdata).each(function(key,value){
			if(value.name == name){

				$(val).each(function(key1,value1){
					if(value1 == "0"){
						val.splice(key1, 1);
					}
				});

				searchdata[key].value = val;
			}
		});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
			data	: searchdata,
			dataType: "json",
			success: function(data) {
				ChangeDropdownOptions(data);
			},
		});
	});

	$("#GenerallySearchForm input.autocompletion").each(function(key,value){

	var name = $(this).prop("name");
	var id = $(this).prop("id");

	$(this).autocomplete({
	minLength: 2,
	delay: 4,
	source:
		function(request,response) {
			value = name + "[" + request.term + "]"

			var data = $("#fakeform").serializeArray();
			data.push({name: name, value: request.term});

			$.ajax({
			url: "<?php echo $autourl;?>",
			dataType: "json",
			data: data,
			success:
				function(data) {
					response(data);
					},
				});
			},
			change: function( event, ui ) {
				if(searchdata.length == 0){
					return false;
				}
				if(ui.item == null){
					$(searchdata).each(function(key,value){
						if(value.name == name){

							CssDropdownSearchStart();

							searchdata.splice(key, 1);
							searchdata.push({id: id, name: name, value: 0});

							$.ajax({
								type	: "POST",
								cache	: true,
								url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
								data	: searchdata,
								dataType: "json",
								success: function(data) {
									ChangeDropdownOptions(data);
								},
							});
							return false;
						}
					});
				}
			},
			close: function(event,ui) {

			},
			select: function(event,ui) {

				CssDropdownSearchStart();

				$(searchdata).each(function(key,value){
					if(value.name == name){
						searchdata.splice(key, 1);
					}
				});

				searchdata.push({id: id, name: name, value: ui.item.key});

				$.ajax({
					type	: "POST",
					cache	: true,
					url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
					data	: searchdata,
					dataType: "json",
					success: function(data) {
						ChangeDropdownOptions(data);
					},
				});
			}
		});
	i++;
	});

	$("#GenerallySearchForm input.autocompletion, #GenerallySearchForm input.date").keyup(function() {

		if($(this).val() == ""){
			var name = $(this).attr("name");
			var id = $(this).attr("id");
			var val = "0";

			CssDropdownSearchStart();

			$(searchdata).each(function(key,value){
				if(value.name == name){
					searchdata.splice(key, 1);
				}
			});

			searchdata.push({id: id, name: name, value: val});

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
				data	: searchdata,
				dataType: "json",
				success: function(data) {
					ChangeDropdownOptions(data);
				},
			});
		}
	});

	$("#GenerallySearchForm input.date").focusout(function() {

		$("#ReportnumberCreatedStart, #ReportnumberCreatedEnd").css("background-color","#fff");

		val = $(this).val();
		id = $(this).attr("id");
		name = $(this).attr("name");

		if($(this).attr("id") == "ReportnumberCreatedStart"){
			Start = $("#ReportnumberCreatedStart").val();
			StartTimestamp = $("#ReportnumberCreatedStartTimestamp").val();
			StartName = "#ReportnumberCreatedStart";
			End = $("#ReportnumberCreatedEnd").val();
			EndTimestamp = $("#ReportnumberCreatedEndTimestamp").val();
			EndName = "#ReportnumberCreatedEnd";
		}
		if($(this).attr("id") == "GenerallyDateOfTestStart"){
			Start = $("#GenerallyDateOfTestStart").val();
			StartTimestamp = $("#GenerallyDateOfTestStartTimestamp").val();
			StartName = "#GenerallyDateOfTestStart";
			End = $("#GenerallyDateOfTestEnd").val();
			EndTimestamp = $("#GenerallyDateOfTestEndTimestamp").val();
			EndName = "#GenerallyDateOfTestEnd";
		}

		if($(this).attr("id") == "ReportnumberCreatedEnd"){
			Start = $("#ReportnumberCreatedStart").val();
			StartTimestamp = $("#ReportnumberCreatedStartTimestamp").val();
			StartName = "#ReportnumberCreatedStart";
			End = $("#ReportnumberCreatedEnd").val();
			EndTimestamp = $("#ReportnumberCreatedEndTimestamp").val();
			EndName = "#ReportnumberCreatedEnd";
		}
		if($(this).attr("id") == "GenerallyDateOfTestEnd"){
			Start = $("#GenerallyDateOfTestStart").val();
			StartTimestamp = $("#GenerallyDateOfTestStartTimestamp").val();
			StartName = "#GenerallyDateOfTestStart";
			End = $("#GenerallyDateOfTestEnd").val();
			EndTimestamp = $("#GenerallyDateOfTestEndTimestamp").val();
			EndName = "#GenerallyDateOfTestEnd";
		}
/*
		ReportnumberCreatedStart = $("#ReportnumberCreatedStart").val();
		ReportnumberCreatedEnd = $("#ReportnumberCreatedEnd").val();

		GenerallyDateOfTestStart = $("#GenerallyDateOfTestStart").val();
		GenerallyDateOfTestEnd = $("#GenerallyDateOfTestEnd").val();
*/
		if(val == ""){
			$(searchdata).each(function(key,value){
				if(value.name == name){

					CssDropdownSearchStart();

					searchdata.splice(key, 1);
					searchdata.push({id: id, name: name, value: 0});

					$.ajax({
						type	: "POST",
						cache	: true,
						url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
						data	: searchdata,
						dataType: "json",
						success: function(data) {
							ChangeDropdownOptions(data);
						},
					});
					return false;
				}
			});
		} else {

/*
			if(StartTimestamp > EndTimestamp){
				$(StartName + "," + EndName).css("background-color","#ff876b");
				return false;
			}
			if(GenerallyDateOfTestStart > GenerallyDateOfTestEnd && GenerallyDateOfTestEnd.length > 0){
				$("#GenerallyDateOfTestStart, #GenerallyDateOfTestEnd").css("background-color","#ff876b");
				return false;
			}

			if(ReportnumberCreatedStart > ReportnumberCreatedEnd && ReportnumberCreatedEnd.length > 0){
				$("#ReportnumberCreatedEnd, #ReportnumberCreatedStart").css("background-color","#ff876b");
				return false;
			}
*/
			CssDropdownSearchStart();

			$(searchdata).each(function(key,value){
				if(value.name == name){
					searchdata.splice(key, 1);
				}
			});

			searchdata.push({id: id, name: name, value: val});

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
				data	: searchdata,
				dataType: "json",
				success: function(data) {
					ChangeDropdownOptions(data);
				},
			});
		}
	});

	$("a.search_history").click(function() {

		CssDropdownSearchStart();

		var data = new Array();
		data.push({name: "history", value: $(this).attr("rev")});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('action'=>'update'), $this->request->projectvars['VarsArray']));?>",
			data	: data,
			dataType: "json",
				success: function(data) {
					ChangeDropdownOptions(data);
				}
			});

		return false;

	});

	$("#ResetThisForm").click(function() {

		CssDropdownSearchStart();

		DeleteFormElements(searchdata);
		searchdata.length = 0;

		searchdatastart.push({name: "search_reset", value: 1});

		var datareset = new Array;
		datareset.push({name: "ajax_true", value: 1});


		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array_merge(array('action'=>'search'), $this->request->projectvars['VarsArray']));?>",
			data	: datareset,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				$("#AjaxSvgLoader").hide();
			},
		});
	});

	$("a.search_history_result").click(function() {

		CssDropdownSearchStart();

		var data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "search_typ", value: 1});
		data.push({name: "history", value: $(this).attr("rev")});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				DeleteFormElements(searchdata);
				searchdata.length = 0;
				$("#AjaxSvgLoader").hide();
				}
			});

		return false;

	});

	$("a.search_history_orders").click(function() {

		CssDropdownSearchStart();

		var data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "search_typ", value: 2});
		data.push({name: "history", value: $(this).attr("rev")});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				DeleteFormElements(searchdata);
				searchdata.length = 0;
				$("#AjaxSvgLoader").hide();
				}
			});

		return false;

	});

	$("#SendThisReportForm").click(function() {

		if(searchdata.length == 0) return false;

		data = searchdata;
		data.push({name: "ajax_true", value: 1});
		data.push({name: "search_typ", value: 1});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				DeleteFormElements(searchdata);
				searchdata.length = 0;
			},
			done: function() {
			}
		});
	});

	$("#SendThisStatistikForm").click(function() {

		if(searchdata.length == 0) return false;

		data = searchdata;
		data.push({name: "ajax_true", value: 1});
		data.push({name: "search_typ", value: 3});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				DeleteFormElements(searchdata);
				searchdata.length = 0;
			},
			done: function() {
			}
		});
	});

	$("#SendThisOrderForm").click(function() {

		if(searchdata.length == 0) return false;

		data = searchdata;
		data.push({name: "ajax_true", value: 1});
		data.push({name: "search_typ", value: 2});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				DeleteFormElements(searchdata);
				searchdata.length = 0;
			},
			done: function() {
			}
		});
	});

$("#testtesttest").click(function() {console.log(searchdata)});

});
</script>

<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/form_multiple_fields_searching');
echo $this->element('js/form_date_fields_searching');
?>
