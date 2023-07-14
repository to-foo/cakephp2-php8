
<script type="text/javascript">

$(document).ready(function() {
                $("a.tooltip_ajax_revision").on("click", function(){return false;});
		$("a.tooltip_ajax_revision").tooltip({
			content: function(callback,ui) {

				var element = $(this);
				var data = $(this).serializeArray();
				var modelfield = $(this).attr('id');
				var modelfield = modelfield.split("/");

				data.push({name: "model", value: modelfield[0]});
				data.push({name: "field", value: modelfield[1]});
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});
				data.push({name: "layout", value: 1});
				data.push({name: "view", value: 1});
				data.push({name: "tooltip", value: 1});

				$.ajax({
						type	: "POST",
						cache	: false,
						url		: element.attr("href"),
						data	: data,
						beforeSend: function(data) {
							$(".modalarea").css("cursor","wait");

						},
						success: function(data) {
							$(".modalarea").css("cursor","inherit");

							callback(data);

						}
					});
			}
		});

	<?php echo join(PHP_EOL, array_map(function($e) {
			return '$("form.editreport select#'.$e.':not(:has(option[rel=\'custom\']))").append(\'$(<option value="&nbsp;" rel="custom">'.__('Custom value').'</option>)\');';
		}, $reportnumber['CustomDropdownFields']));
	?>

	$("select.hasDependencies").on("change", function() {

		var noSave = $(this).data('noSave');

		$(this).removeData('noSave');

		// Dependency-Link aktualisieren
		link = $(this).parent().find(".modal.dependency");

		if($(this).val() == "") {
			link.prop("disabled", true);
		} else {
			link.prop("disabled", false);//.attr("href", link.attr("href").replace(/[\d]+$/, $(this).val()));
		}

		// Eventuell vorhandene abhängige Felder löschen
		$(".dependency_"+$(this).attr("id")).parent().find(".dependencyReplaced").removeClass("dependencyReplaced").removeAttr("style");
		$(".dependency_"+$(this).attr("id")).remove();

		testfunktion = $(this).attr("name").match(/data\[(.*?)\](\[0\])*\[(.*?)\]/)

		if($(this).attr("multiple") == "multiple") {
			$("#content .edit a.print").data('prevent',1);//.prop('disabled', true).attr('disabled', 'disabled');
			$.ajax({
				url: "<?php echo Router::url(array_merge(array('controller' => 'dependencies', 'action' => 'get'), array_slice($VarsArray, 0, 10))); ?>",
				type: "post",
				data: {
					ajax_true: 1,
					item: testfunktion[1]+'0'+testfunktion[3],
					multiple: $(this).val()
				},
				dataType: "json",
				success: function(data) {

				},
				complete: function(data) {
					$("#content .edit a.print").removeData('prevent');//.prop('disabled', false).removeAttr('disabled', 'disabled').;
				}
			});
		}

		if(test = testfunktion && $(this).attr("multiple") != "multiple") {

			if($(this).val().length == 0) {

				$("#content .edit a.print").data('prevent',1);//.prop('disabled', true).attr('disabled', 'disabled');
				$.ajax({
					url: "<?php echo Router::url(array_merge(array('controller' => 'dependencies', 'action' => 'get'), array_slice($VarsArray, 0, 10))); ?>",
					type: "post",
					data: {ajax_true: 1, item:test[1]+'0'+test[3]},
					dataType: "json",
					success: function(data) {

						// Wenn keine abhängigen Werte gefunden werden, abbrechen

						if(typeof data !== "object" || data.length == 0) return;
						for(var field in data) {
							if(field == "item") continue;

							Field = field.replace(/\_\w/g, function(match) {
								return match.replace("_", "").toUpperCase();
							}).replace(/^\w/, function(match) {
								return match.toUpperCase();
							});

							elem = $("[id$='"+Field+"']");
							$(elem).removeClass('dependencyReplaced').removeAttr('style').closest('div').find('.dependency').remove();

							// Abhängige Felder leeren und verstecken, wenn keine abhängigen Werte existieren
							if(data[field].length == 0) {
								//if(elem.val().length != 0) elem.val('').trigger('change');
								//elem.parent('div').hide();//show();
								if(noSave) elem.val('').parent('div').show();
								else elem.val('').trigger('change').parent('div').show();
							}
						}
					},
					complete: function(data) {
						$("#content .edit a.print").removeData('prevent');//.prop('disabled', false).removeAttr('disabled', 'disabled').;
					}
				});
			} else if($(this).val().match(/^[0-9]+$/)) {
				$("#content .edit a.print").data('prevent',1);//.prop('disabled', true).attr('disabled', 'disabled');
				$.ajax({
					url: "<?php echo Router::url(array_merge(array('controller' => 'dependencies', 'action' => 'get'), array_slice($VarsArray, 0, 10))); ?>/" + parseInt($(this).val()),
					type: "post",
					data: {ajax_true: 1, item:test[1]+'0'+test[3]},
					dataType: "json",
					success: function(data) {
						// Wenn keine abhängigen Werte gefunden werden, abbrechen
						if(typeof data !== "object" || data.length == 0) return;

						for(var field in data) {
							if(field == "item") continue;

							Field = field.replace(/\_\w/g, function(match) {
								return match.replace("_", "").toUpperCase();
							}).replace(/^\w/, function(match) {
								return match.toUpperCase();
							});

							elem = $("[id$='"+Field+"']");
							$(elem).removeClass('dependencyReplaced').removeAttr('style').closest('div').find('.dependency').remove();

							// Abhängige Felder leeren und verstecken, wenn keine abhängigen Werte existieren
							if(data[field].length == 0) {
								if(noSave) elem.val('').closest('div').show();
								else elem.val('').trigger('change').closest('div').show();

								continue;
							} else {
								if($.inArray($(elem).val(), data[field]) == -1) {
									if(noSave) elem.val('');
									else elem.val('').trigger('change');
								}
								elem.closest('div').show();
							}


							// vorhandenes Datum in Liste der abhängigen Werte übernehmen
							// data[field].push(elem.val());

							// Array Unique
							data[field] = $.grep(data[field], function(el, index) {
						        return index === $.inArray(el, data[field]);
						    });

							Elem = '<select <?php if($reportnumber['Reportnumber']['status'] > 0) echo 'disabled="disabled" ';?>id="'+Field+'" class="dependency dependency_'+data["item"]+'" name="'+elem.attr("name")+'">';
							for(var _val in data[field]) {

								Elem += '<option value="'+data[field][_val]+'"';

								if(data[field][_val] == $(elem).val()){
									Elem +=	'selected="selected"';
								}

								Elem +=	'>';
								Elem += data[field][_val];
								Elem += '</option>';

							}

							Elem += '</select>';

							if(elem.css("display") != "none") {
								$(elem).parent().find(":not(label)").css("display", "none").addClass("dependencyReplaced");
								$(elem).parent().append(Elem);
							}

							if(data[field].length == 2) {
								if($(elem).val() != data[field][1]) {
									if(noSave) $("#"+Field).val(data[field][1]);
									else $("#"+Field).val(data[field][1]).trigger("change");
								}
							}
						}
					},
					complete: function(data) {
						$("#content .edit a.print").removeData('prevent');//.prop('disabled', false).removeAttr('disabled', 'disabled').;
					}
				});
			}
		}
	});

	$("select").on("change", function() {
		if($(this).find("option:selected").attr("rel")=="custom") {
			style = {
				"border": 0,
				"width": $(this).width() - 1,
				"height": $(this).height(),
				"position": "absolute",
				"top": $(this).position().top + 1,
				"left": $(this).position().left + 1
			};

			id = $(this).attr("id");
			name = $(this).attr("name");

			$(this).parent().append($('<input id="'+id+'" name="'+name+'" type="text" class="customValue" />').css(style).attr("rel", id)).find(".customValue").focusout(function() {
				rel = "#"+$(this).attr("rel");
				val = $(this).val().replace('"',"'");

				if(val != "") {
					$(this).parent().find(rel+' option:last-child').before('<option value="'+val+'">'+val+'</option>').parent().val(val);
				}
				else {
					$(rel).attr("disabled",false);
					$(rel).closest("div").css("background-image","none");
				}

				$(this).remove()
			}).focus();

		}
	});

	if(typeof Validation !== "undefined") {
		$(Validation).each(function(key,value){
			$(value).each(function(key2,value2){
			});
		});
	}
	if(typeof ShowValidation !== "undefined") {
	}
});
</script>

<?php
$url = $this->Html->url(array('controller'=>'reportnumbers','action'=>'evaluation',
	$this->request->projectvars['VarsArray'][0],
	$this->request->projectvars['VarsArray'][1],
	$this->request->projectvars['VarsArray'][2],
	$this->request->projectvars['VarsArray'][3],
	$this->request->projectvars['VarsArray'][4],
	$this->request->projectvars['VarsArray'][5],
	)
);
?>
<script type="text/javascript">
$(document).ready(function(){

	var SaveQuest = 0;
	var rel_menue = null;

	if($("#Li_TestingArea").hasClass("active")){
		$("#EvaluationArea").css("background-image","url(img/indicator.gif)");
		$("#EvaluationArea").css("background-repeat","no-repeat");
		$("#EvaluationArea").css("background-position","center center");
		$("#EvaluationArea").css("min-height","3em");


		var url = "<?php echo $url;?>";
		var data = $("#fakeform").serializeArray();
		data.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			success: function(data) {
		    	$("#EvaluationArea").html(data);
		    	$("#EvaluationArea").show();
			}
		});
	}

	$("a.TestingArea").one("click", function() {

		$("#EvaluationArea").css("background-image","url(img/indicator.gif)");
		$("#EvaluationArea").css("background-repeat","no-repeat");
		$("#EvaluationArea").css("background-position","center center");
		$("#EvaluationArea").css("min-height","3em");


		var url = "<?php echo $url;?>";
		var data = $("#fakeform").serializeArray();
		data.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			success: function(data) {
		    	$("#EvaluationArea").html(data);
		    	$("#EvaluationArea").show();
			}
		});
	});

	$("div.reportnumbers a, div.buttons input").click(function() {
		$("ul.editmenue li a").each(function(){
			if($(this).hasClass("active")){
			}
		});
	});

	$("form.editreport input.hide_box").on('change', function() {
		data = [{'name': 'ajax_true', 'value': 1}, {'name': 'hideField', 'value': 'hideField' }, {'name': 'field', 'value': $(this).attr('id') }, {'name': 'value', 'value': $(this).prop('checked') }];

		$.ajax({
			'url': '<?php echo Router::url(array_merge(array('controller'=>'reportnumbers', 'action'=>'edit'), $VarsArray)); ?>',
			'type': 'post',
			'data': data,
			'success': function(data) {
			}
		});
	});
});
</script>
<?php $testingmethod = ucfirst($reportnumber ['Testingmethod']['value']);?>
<script type="text/javascript">

$(document).ready(function() {

// Prüfer und Prüfaufsicht dürfen nicht gleich sein 24.08.2017..................


   $('#Report<?php echo $testingmethod?>GenerallySupervision option').each(function(index, elem){
                if($(elem).text() == $('#Report<?php echo $testingmethod?>GenerallyExaminerName option:selected').text() && $('#Report<?php echo $testingmethod?>GenerallyExaminer option:selected').val() !== ''){
                    var value = $(elem).val();
                    $('#Report<?php echo $testingmethod?>GenerallySupervision [value='+value+']').prop('disabled', true);
                }
            });


       $('#Report<?php echo $testingmethod?>GenerallySupervision option:selected').each(function(){
            $('#Report<?php echo $testingmethod?>GenerallyExaminerName option').each(function(index, elem){
                if($(elem).text() == $('#Report<?php echo $testingmethod?>GenerallySupervision option:selected').text() && $('#Report<?php echo $testingmethod?>GenerallySupervision option:selected').val() !== ''){
                    var value = $(elem).val();
                    $('#Report<?php echo $testingmethod?>GenerallyExaminer [value='+value+']').prop('disabled', true);
                }
            });
        });
     $("#Report<?php echo $testingmethod?>GenerallyExaminerName").on("change", function() {
        $('#Report<?php echo $testingmethod?>GenerallySupervision option').removeAttr('disabled');
         $('#Report<?php echo $testingmethod?>GenerallyExaminerName option:selected').each(function(){
            $('#Report<?php echo $testingmethod?>GenerallySupervision option').each(function(index, elem){
                if($(elem).text() == $('#Report<?php echo $testingmethod?>GenerallyExaminer option:selected').text() && $('#Report<?php echo $testingmethod?>GenerallyExaminerName option:selected').val() !== ''){
                    var value = $(elem).val();
                    $('#Report<?php echo $testingmethod?>GenerallySupervision [value='+value+']').prop('disabled', true);
                }
            });
        });
   });
   $("#Report<?php echo $testingmethod?>GenerallySupervision").on("change", function() {
        $('#Report<?php echo $testingmethod?>GenerallyExaminerName option').removeAttr('disabled');
         $('#Report<?php echo $testingmethod?>GenerallySupervision option:selected').each(function(){
            $('#Report<?php echo $testingmethod?>GenerallyExaminerName option').each(function(index, elem){
                if($(elem).text() == $('#Report<?php echo $testingmethod?>GenerallySupervision option:selected').text() && $('#Report<?php echo $testingmethod?>GenerallySupervision option:selected').val() !== ''){
                    var value = $(elem).val();
                    $('#Report<?php echo $testingmethod?>GenerallyExaminerName [value='+value+']').prop('disabled', true);
                }
            });
        });
   });

<?php
/*if($reportnumber['Reportnumber']['status'] > 0){
	echo '$("form.editreport input,form.editreport  select,form.editreport  textarea,form.editreport  button").attr("disabled", "disabled");';
}*/
/*if(isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1){

	echo '$("form.editreport input,form.editreport  select,form.editreport  textarea,form.editreport  button").removeAttr("disabled");';
	echo '$("#container form fieldset div").css("background-color","#f29d21");';
}*/
?>

 });
 //06.09.2017...................................................................
 </script>
