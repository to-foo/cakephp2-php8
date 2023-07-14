<div class="modalarea detail">
<h2><?php echo __('Edit order')?> <?php echo $headline;?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose(1.5);
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
	}
?>
<?php echo $this->Form->create('Order', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php echo $this->ViewData->EditModulData($this->request->data,$orderoutput,$locale,__('Order'));?>
	</fieldset>
	<fieldset class="multiple_field">
	<?php echo $this->Form->input('Testingcomp',array('label' => __('Involved companies',true),'options' => $testingcomps,'selected' => $this->request->data['Testingcomp']['selected']));?>
    </fieldset>
	<fieldset class="multiple_field">
	<?php if(isset($emailaddress) && count($emailaddress) > 0) echo $this->Form->input('Emailaddress',array('label' => __('Involved email adresses',true),'options' => $emailaddress,'selected' => $this->request->data['Emailaddress']['selected']));?>
  </fieldset>
	<fieldset>
	<?php
	if(count($developments) > 0)echo $this->Form->input('Development', array('type' => 'select','empty' => __('choose one'),'options' => $developments,'selected' => $this->request->data['Development']['selected']));
	?>
	</fieldset>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="clear" id="testdiv"></div>
<?php // echo $this->element('fast_save_common_js'); ?>
<script type="text/javascript">
$(document).ready(function() {

	$("select.hasDependencies").on("change", function() {
		var noSave = $(this).data('noSave');
		$(this).removeData('noSave');

		// Dependency-Link aktualisieren
		link = $(this).parent().find(".modal.dependency");

		if($(this).val() == "") {
			link.prop("disabled", true);
		} else {
			link.prop("disabled", false);
		}

		// Eventuell vorhandene abhängige Felder löschen
		$(".dependency_"+$(this).attr("id")).parent().find(".dependencyReplaced").removeClass("dependencyReplaced").removeAttr("style");
		$(".dependency_"+$(this).attr("id")).remove();

		if($(this).val().match(/^[0-9]+$/)) {
			$("#content .edit a.print").data('prevent',1);
			$.ajax({
				url: "<?php echo Router::url(array_merge(array('controller' => 'dependencies', 'action' => 'get'), array_slice($VarsArray, 0, 10))); ?>/" + parseInt($(this).val()),
				type: "post",
				data: {ajax_true: 1, item:$(this).attr("id")},
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

						// Abhängige Felder leeren und verstecken, wenn keine abhängigen Werte existieren
						if(data[field].length == 0) {
							if(noSave) elem.val('').closest('div').hide();
							else elem.val('').trigger('change').closest('div').hide();
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

						Elem = '<select id="'+Field+'" class="dependency dependency_'+data["item"]+'" name="'+elem.attr("name")+'">';
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
					$("#content .edit a.print").removeData('prevent');
				}
			});
		} else if($(this).val().length != 0) {
			$("#content .edit a.print").data('prevent',1);
			$.ajax({
				url: "<?php echo Router::url(array_merge(array('controller' => 'dependencies', 'action' => 'get'), array_slice($VarsArray, 0, 10))); ?>",
				type: "post",
				data: {ajax_true: 1, item:$(this).attr("id")},
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

						// Abhängige Felder leeren und verstecken, wenn keine abhängigen Werte existieren
						if(data[field].length == 0) {
							//if(elem.val().length != 0) elem.val('').trigger('change');
							//elem.parent('div').hide();//show();
							if(noSave) elem.val('').parent('div').hide();
							else elem.val('').trigger('change').parent('div').hide();
						}
					}
				},
				complete: function(data) {
					$("#content .edit a.print").removeData('prevent');
				}
			});
		} else {
			$("#content .edit a.print").data('prevent',1);
			$.ajax({
				url: "<?php echo Router::url(array_merge(array('controller' => 'dependencies', 'action' => 'get'), array_slice($VarsArray, 0, 10))); ?>",
				type: "post",
				data: {ajax_true: 1, item:$(this).attr("id")},
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

						if(noSave) elem.val('').parent('div').show();
						else elem.val('').trigger('change').parent('div').show();
					}
				},
				complete: function(data) {
					$("#content .edit a.print").removeData('prevent');
				}
			});
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

	$("select.hasDependencies").data('noSave', true).trigger("change");
});
</script>
<?php
echo $this->element('js/form_checkbox');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_modal_request');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
?>
