<?php
$TestinstructionUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'testinstruction'), $this->request->projectvars['VarsArray']));
$ReasonUrl = $this->Html->url(array_merge(array('controller' => 'testinginstructions','action' => 'reason'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisTestinstructionUrl', array('type' => 'hidden','value' => $TestinstructionUrl));
echo $this->Form->input('ThisReasonUrl', array('type' => 'hidden','value' => $ReasonUrl));
?>
<script type="text/javascript">
$(document).ready(function() {

	function CreateTestinstructionInfo(data){

		TestinstructionLink  = '<div class="bottom_hint_area_el testinstruction_error_link">';
		TestinstructionLink += '<b><?php echo __('Testing instructions',true);?></b><br>';
		TestinstructionLink += '<span class="count"></span>';
		TestinstructionLink += ' <?php echo __('irregularities',true);?>';
		TestinstructionLink += '</div>';

		$("div#bottom_hint_area").empty();
		$("div#bottom_hint_area").append($(TestinstructionLink));

		error_link_position = $("div.testinstruction_error_link").position();

		$("div.testinstruction_error").css("left",error_link_position.left + "px");

		$.each(data.data,function(key,value){
			$.each(value,function(_key,_value){

				$("#" + key + _value.field).parent("div").prepend('<a href="javascript:" id="Instr'+key + _value.field+'" title="Testinstructions" class="testinstruction_info" rel="'+key+'" rev="'+_key+'"></a>');

				if(_value.html_field == "dropdown"){
					if(!$("#" + key + _value.field).is("select")) {
						if($("#" + key + _value.field).val().length == 0){

						} else {

						}
					} else {

						if($("#" + key + _value.field).find('option:selected').text() == _value.value){
							$("#Instr" + key + _value.field).css("background-image","url(../svg/icon_check_one.svg)");
						}

						if($("#" + key + _value.field).find('option:selected').text() != _value.value){
							$("#" + key + _value.field).append('<option value="' + _value.value + '">' + _value.value + '</option>');
						}
					}
				} else {
					if($("#" + key + _value.field).val() == _value.value && _value.value.length > 0){
						$("#Instr" + key + _value.field).css("background-image","url(../svg/icon_check_one.svg)");
					}
				}

				if(_value.rule == true){
					if(_value.current != _value.value && _value.current.length != undefined){
						$("#" + key + _value.field).parent("div").addClass("testinstruction_error");
					}
				}
			});
		});

		TestinstructionErrorCounter();

	}

	function TestinstructionTooltipStart(data){

		$(document).on('click', '.testinstruction_info', function () {

			$(this).addClass("on");
			$(this).css("background-image","url(img/icon_violet_negativ_close.png)");

			$(this).tooltip({

				items: '.testinstruction_info.on',
				tooltipClass: "testinstruction_tooltip",

				content: function(callback) {

					rel = this.rel;
					rev = this.rev;

					callback  = "<p>";
					callback += "<b>" + data.data[rel][rev]["title"] + "</b>";
					callback += "</p>";

					callback += "<p>";
					if(data.data[rel][rev]["rule"] == true) callback += data.data[rel][rev]["rule_description"];
					if(data.data[rel][rev]["option"] == true) callback += data.data[rel][rev]["option_description"];
					if(data.data[rel][rev]["hint"] == true) callback += data.data[rel][rev]["hint_description"];
					callback += "</p>";

					if(data.data[rel][rev]["remark"].length > 0){
						callback += "<p>";
						callback += data.data[rel][rev]["remark"];
						callback += "</p>";
					}

					callback += "<p>";
					callback += data.data[rel][rev]["value_description"] + ":<br>";

					if(data.data[rel][rev]["data_description"].length > 0){
						callback += "<span class='cursive'>";
						callback += data.data[rel][rev]["data_description"];
						callback += "</span><br>";
					}

					if(data.data[rel][rev]["value"].length > 0){
						callback += "<span class='the_value' istr-data-model='"+rel+"' istr-data-field='"+rev+"' istr-data-id='"+rel+data.data[rel][rev]["field"]+"'>";
						callback += data.data[rel][rev]["value"];
						callback += "</span>";
						callback += "<br>";
						callback += data.data[rel][rev]["current_description"] + ":<br>";
					}

					if(data.data[rel][rev]["current"] != false) callback += data.data[rel][rev]["current"];

					callback += "</p>";

					if($("#" + rel + data.data[rel][rev]["field"]).parent("div").hasClass("testinstruction_error")){
						callback += "<p><b>" + data.data[rel][rev]["error_description"] + "</b></p>";
					}

					return callback;

				}
			});

			$(this).trigger('mouseenter');

		});

		$(document).on("click", ".on", function () {
			$(this).tooltip("close");
			$(this).removeClass("on");
			$(this).css("background-image","url(img/icon_violet_negativ_hint.png)");
		});

		$(".testinstruction_info").on('mouseout', function (e) {
			e.stopImmediatePropagation();
		});

	}

	function PutTestinstuctionData(data){
		$(document).on("click", "span.the_value", function () {

			dataid = $(this).attr("istr-data-id");
			datamodel = $(this).attr("istr-data-model");
			datafield = $(this).attr("istr-data-field");
			dataval = $(this).text();

			if($("#" + dataid).is("input")){
				$("#" + dataid).val(dataval);
			}
			if($("#" + dataid).is("select")){

				$("#" + dataid + " option").each(function() {
					dropdown_exist = false;
    			if(this.text == dataval){
						dropdown_exist = this.value;
					}
				});

				if(dropdown_exist == false) $("#" + dataid +" option[value='" + dataval + "']").attr('selected',true);
				else $("#" + dataid +" option[value='" + dropdown_exist + "']").attr('selected',true);
			}

			$("#" + dataid).parent("div").removeClass("testinstruction_error");

			data.data[datamodel][datafield]["current"] = dataval;
			data.data[datamodel][datafield]["error_description"] = false;

			$("#Instr" + dataid).tooltip("close");
			$("#Instr" + dataid).removeClass("on");
			$("#Instr" + dataid).css("background-image","url(img/icon_violet_negativ_hint.png)");
			$("#Dev" + dataid).hide('slow', function(){ $("#Dev" + dataid).remove(); });

			TestinstructionErrorCounter();

			$("#" + dataid).trigger("change");

		});
	}


	function ManageErrorBox(data){

		$("input, select").on('change', function (e) {

			ModelField = $(this).attr("name").split("[");
			ModelId = $(this).attr("id");
			Model = ModelField[1].split("]");
			Field = ModelField[2].split("]");

			Model = Model[0];
			Field = Field[0];

			if(data.data.length == 0) return false;
			if(data.data[Model] == undefined) return false;
			if(data.data[Model][Field] == undefined) return false;

			html_field = data.data[Model][Field]["html_field"];
			instraction_value = data.data[Model][Field]["value"];

			if(data.data[Model][Field]["rule"] != true) return false;

			DeleteReasonTable(data.data[Model][Field]['data_id']);

			if(html_field == "dropdown") {

				if($(this).is("select")) {
					this_value = $(this).find('option:selected').text();
				} else {
					this_value = $(this).val();
				}

				if(this_value == instraction_value){
					$("#Dev" + Model + data.data[Model][Field]["field"]).hide('slow', function(){ $("#Dev" + Model + data.data[Model][Field]["field"]).remove(); });
					$("#Dev" + Model + data.data[Model][Field]["field"]).remove();
					$("#Instr" + ModelId).css("background-image","url(../svg/icon_check_one.svg)");
				} else {

					$("#Dev" + Model + data.data[Model][Field]["field"]).remove();

					if(this_value.length == 0){
						TestinstructionErrorCounter();
						return false;
					}

					$(".testinstruction_error").show();

					devlist  = '<li style="display:none;" class="main" id="Dev' + Model + data.data[Model][Field]['field'] + '">';
					devlist += '<b>' + data.data[Model][Field]['title'] + '</b>';
					devlist += '<ul class="field">';
					devlist += '<li><span>' + data.data[Model][Field]['value_description'] + '</span>: ' + data.data[Model][Field]['value'] + '</li>';
					devlist += '<li><span>' + data.data[Model][Field]['current_description'] + '</span>: ' + this_value + '</li>';
					devlist += '<li class="reasons"><span class="round editable_reason"  data-id="' + data.data[Model][Field]['data_id'] + '"><?php echo __('Reason',true);?></span></li>';
					devlist += '</ul></li>';

					$(".testinstruction_error p b").show('slow', function(){});

					$(devlist).appendTo("div.testinstruction_error ul.model").show();
					$(".testinstruction_error").animate({ scrollTop: $(".testinstruction_error").height() }, "slow");
					$("#Instr" + ModelId).css("background-image","url(../svg/icon_hint.svg)");

					ReasonHintEffect(Model,data.data[Model][Field]["field"]);

				}

				EditableLink();
			}

			if(html_field == false) {

				this_value = $(this).val();

				if(this_value == instraction_value){
					$("#Dev" + Model + data.data[Model][Field]["field"]).hide('slow', function(){ $("#Dev" + Model + data.data[Model][Field]["field"]).remove(); });
					$("#Dev" + Model + data.data[Model][Field]["field"]).remove();
					$("#Instr" + ModelId).css("background-image","url(../svg/icon_check_one.svg)");
			} else {

					$("#Dev" + Model + data.data[Model][Field]["field"]).remove();

					if(this_value.length == 0) return false;

					devlist  = '<li style="display:none;" class="main" id="Dev' + Model + data.data[Model][Field]['field'] + '">';
					devlist += '<b>' + data.data[Model][Field]['title'] + '</b>';
					devlist += '<ul class="field">';
					devlist += '<li><span>' + data.data[Model][Field]['value_description'] + '</span>: ' + data.data[Model][Field]['value'] + '</li>';
					devlist += '<li><span>' + data.data[Model][Field]['current_description'] + '</span>: ' + this_value + '</li>';
					devlist += '<li class="reasons"><span class="round editable_reason"  data-id="' + data.data[Model][Field]['data_id'] + '"><?php echo __('Reason',true);?></span></li>';
					devlist += '</ul></li>';
					$(".testinstruction_error p b").show('slow', function(){});
					$(devlist).appendTo("div.testinstruction_error ul.model").show();
					$(".testinstruction_error").animate({ scrollTop: $(".testinstruction_error").height() }, "slow");
					$("#Instr" + ModelId).css("background-image","url(../svg/icon_hint.svg)");

					$(".testinstruction_error").show();
					ReasonHintEffect(Model,data.data[Model][Field]["field"]);

				}

				EditableLink();
			}

			data.data[Model][Field]["current"] = $(this).val();

			if($('.testinstruction_error ul.model li.main').length == 0){
				$('.testinstruction_error p b').text("<?php echo __('No deviations detected',true);?>");
			}
			if($('.testinstruction_error ul.model li.main').length > 0){
				$('.testinstruction_error p b').text("<?php echo __('Deviations detected',true);?>");
			}

			TestinstructionErrorCounter();

		});
	}

	function TestinstructionErrorCounter(){

		instructin_error_count = $('.testinstruction_error ul.model li.main').length;

		$(".testinstruction_error_link span.count").html(instructin_error_count);

		if(instructin_error_count == 0) $(".testinstruction_error_link").css("background-image","url(../svg/icon_check_one.svg)");
		else $(".testinstruction_error_link").css("background-image","url(../svg/icon_hint.svg)");

//		$(".testinstruction_error_link").effect( "shake", { direction: "left", times: 3, distance: 5}, 1000 );

		$(document).on("click", "div.testinstruction_error_link", function () {
			$(".testinstruction_error").show();
		});

		$(document).on("click", "div.testinstruction_error a.close_window", function () {
			$(".testinstruction_error").hide();
		});
	}

	function EditableLink(){

		$(".editable_reason").editable($("#ThisReasonUrl").val(), {
	    	submit : 'OK',
    		cancel : 'Cancel',
				type   : 'textarea',
	    	cssclass : 'testinstruction_editable',
	    	cancelcssclass : 'editable_cancel',
	    	submitcssclass : 'editable_submit',
/*
				onblur: function(value, settings) {
					alert("test");
				},
*/
				submitdata : function(data) {
					var send = new Array();
					send.push({data_id: $(this).attr('data-id')});
					return send[0];
	    	},
				callback : function(data) {
//				console.log(data);
	    	}
		});
	}

	function DeleteReasonTable(data_id){

		data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_id", value: data_id});
		data.push({name: "value", value: null});
		data.push({name: "delete", value: 1});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: $("#ThisReasonUrl").val(),
			data	: data,
			dataType: "json",
			success: function(data) {
//				console.log(data);
			},
		});

	}

	function ReasonHintEffect(model,data_id){

		$(".testinstruction_error ul.model li#Dev" + model + data_id + " li.reasons span.editable_reason")
			.animate(
				{
					backgroundColor: "#a122c0;",
					color: "#ffffff",
				},
				2000
			)
			.animate({
				backgroundColor: "#ffffff",
				color: "#a122c0",
			},
			1000
		);

	}

	$.ajax({
		type	: "POST",
		cache	: true,
		url		: $("#ThisTestinstructionUrl").val(),
		data	: null,
		dataType: "json",
		success: function(data) {

			CreateTestinstructionInfo(data);
			TestinstructionTooltipStart(data);
			ManageErrorBox(data);
			PutTestinstuctionData(data);
			EditableLink();

		},
	});

});
</script>
