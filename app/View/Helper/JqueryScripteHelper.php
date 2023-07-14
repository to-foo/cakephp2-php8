<?php
class JqueryScripteHelper extends AppHelper {

	var $helpers = array('Html');

	public function SendAjaxModal($url) {
	}

	public function DialogClose($time = 2000) {
	if($time < 10) $time *= 1000;
	$output  = null;
	$output .= '<script type="text/javascript">
				function closemodal(){
					$(document).ready(function(){
						$("#dialog").dialog();
						if ($("#dialog").dialog("isOpen") === true) {
							$("#dialog").dialog("close");
						}
					});
				}
				setTimeout(closemodal, ' . $time . ');
				</script>';
	return $output;
	}

	public function LoadModal($url) {
	$output  = null;
	$output .= '<script type="text/javascript">';
	$output .= '$(document).ready(function(){';
	$output .= '$("#dialog").load("'.$url.'",{"ajax_true": 1})';
	$output .= '});';
	$output .= '</script>';
	return $output;
	}

	public function ScrollToElement($element,$time) {
	$output  = null;
	$output .= '<script type="text/javascript">
					$(document).ready(function(){
						$("html, body").animate({
						scrollTop: $("'.$element.'").offset().top
						}, '.$time.');
					});
				</script>';
	return $output;
	}

	public function GlobalErrorHandler() {
		$output = null;

		if(!defined('AJAX_SETUP')) {
			define('AJAX_SETUP', true);

			$output .= '
				<script type="text/javascript">
				if(!window.console) {
					window.console = {debug: function() {}, error: function() {}, info: function() {}, log: function() {}};
				}

				$.ajaxSetup({
					global: true,
					type: "POST"
				});

				$(document).ajaxError(function(event, request, setting, error) {

					switch(request.status) {
						case 403:
							location.href = "'.$this->Html->url(array('controller'=>'users','action'=>'loggedin')).'";
							break;

						default:
							$("#container").html(request.responseText).show();
							$(".ui-dialog").hide();
							break;
					}
				});
				</script>
			';
		}

		return $output;
	}

	public function AskForceSave($formid) {
		$output = null;
		$output .= '
		<script type="text/javascript">
			$(document).ready(function($ev) {
				if($("#'.$formid.'").length) {
					if(confirm("'.__('The supplied data contains errors. Do you want to save anyway?').'"))
					{
						if($("#'.$formid.'").append("<input type=\"hidden\" name=\"data[force_save]\" value=\"1\">").triggerHandler("submit") == undefined) {
							$("#'.$formid.'").submit();
						}
					}
				}
			});
		</script>
		';
		return $output;
	}

	public function SpecialCharacters($thisID) {

	$output  = null;

	$output .= '
	<script type="text/javascript">
		$(document).ready(function(){

			$(".specialcharacter a").click(function(){
				//if($("#content .reportnumbers .edit .specialchars").attr("rel") == "") return false;
				if("'.$thisID.'" == "") return false;

				if($("#' . $thisID . '").prop("disabled")) return false;
				var newValue = $("#' . $thisID . '").val() + $(this).text();
				$("#dialog").dialog().dialog("close");
				$("#' . $thisID . '").val(newValue).trigger("change");

				return false;
			});

			$(".mymodal").click(function(){

				$("#dialog").load($(this).attr("href"), {
						"ajax_true": 1
					})
				return false;
			});

			$("form.dialogform").bind("submit", function() {

				var data = $(this).serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});

				$.ajax({
						type	: "POST",
						cache	: false,
						url		: $(this).attr("action"),
						data	: data,
						success: function(data) {
		    				$("#dialog").html(data);
		    				$("#dialog").show();
						}
					});
					return false;
				});

		});
	</script>
	';

	return $output;

	}

	public function SpecialCharactersHelper() {

	$output  = null;
	$output .= $this->GlobalErrorHandler();

	$output .= '<script type="text/javascript">
				$(document).ready(function(){
					$("#content .reportnumbers .edit .specialchars")
						.attr("rel", "")
						.removeData("timeout")
						.off("click")
						.unbind("click")
						.on("click", function() {

							var modalheight = Math.ceil(($(window).height() * 90) / 100);
							var modalwidth = Math.ceil(($(window).width() * 90) / 100);

							var dialogOpts = {
								modal: false,
								width: modalwidth,
								height: modalheight,
								autoOpen: false,
								draggable: true,
								resizeable: true
							};

							$("#dialog").dialog(dialogOpts);

							$("#dialog").load($(this).attr("href"), {
								"ajax_true": 1,
								"thisID": $(this).attr("rel"),
							});
							$("#dialog").dialog("open");
							return false;
						});

					$(".reportnumbers input[type=\'text\'], .reportnumbers textarea").on({
						"focus": function() {
							$("#content .reportnumbers .edit .specialchars").attr("rel", $(this).attr("id"));
						}
					});
				});
				</script>';
		return $output;
	}

	public function RefreshAfterDialogTimeout($reportnumberID,$evalutionID,$FormName,$Time) {

			$output = null;
			$output .= $this->GlobalErrorHandler();
			$output  .= '
			<script type="text/javascript">
				$(document).ready(function(){
			';

 			$output .= 'setTimeout("';

			if(is_array($FormName)){
				$output .= '$(\'#container\').load(\'' . $this->Html->url(array('controller' => $FormName['controller'], 'action' => $FormName['action'],$reportnumberID, $evalutionID)) . '\', {\'ajax_true\': 1}) ';
			}

		$output .= '",'.$Time.');';
		$output .= '					});
			</script>
		';

		return $output;
	}

	public function RefreshAfterDialog($reportnumberID,$evalutionID,$FormName) {

			$output = null;
			$output .= $this->GlobalErrorHandler();
			$output  .= '
			<script type="text/javascript">
				$(document).ready(function(){
			';
			if(is_array($FormName)){
				if(isset($FormName['terms'])){
					$thisURL = str_replace("%2C", "/", $this->Html->url(array('controller' => $FormName['controller'], 'action' => $FormName['action'].'/'.$FormName['terms'])));
					$output .= '$("#container").load("' . $thisURL . '", {"ajax_true": 1}) ';
					}
				else {
					$output .= '$("#container").load("' . $this->Html->url(array('controller' => $FormName['controller'], 'action' => $FormName['action'],$reportnumberID)) . '", {"ajax_true": 1}) ';
					}
			}

			else {
				$output  .= '
/*							var data = $("'.$FormName.'").serializeArray(); */
							var data = $("#fakeform").serializeArray();
							data.push({name: "ajax_true", value: 1});
							data.push({name: "StopSafe", value: 1});

							$.ajax({
								type	: "POST",
								cache	: false,
								url		: $("'.$FormName.'").attr("action"),
								data	: data,
								success: function(data) {
		    					$("#container").html(data);
		    					$("#container").show();
								}
							});
							return false;
				';
			}

		$output .= '					});
			</script>
		';

		return $output;
	}

	public function RefreshDialogAfterDialog($FormName,$time = 0) {

		$output = null;
		$output .= $this->GlobalErrorHandler();

		$output  .= '<script type="text/javascript">';
		$output  .= '$(document).ready(function(){';
			if(is_array($FormName)){
				if(isset($FormName['terms'])){
					$thisURL = str_replace("%2C", "/", $this->Html->url(array('controller' => $FormName['controller'], 'action' => $FormName['action'].'/'.$FormName['terms'])));

					$output .= 'setTimeout( function(){';
					$output .= '$("#dialog").load("' . $thisURL . '", {"ajax_true": 1}) ';
					$output .= '} , ' . $time . ');';
					}
			}

		$output .= '});';

        $output .= '</script>';

		return $output;
	}

	public function TotalAndRoundHt($va1) {

	// nur bei HT-Prüfberichten
	if($va1 != 'ht'){
		return;
	}

	$output  = null;
	$output .='<script type="text/javascript">
	$(function(){

	$("#ReportHtEvaluation0Wert1, #ReportHtEvaluation0Wert2, #ReportHtEvaluation0Wert3").change(function() {

		var x = 0;

		var wert1 = Number($("#ReportHtEvaluation0Wert1").val());
		var wert2 = Number($("#ReportHtEvaluation0Wert2").val());
		var wert3 = Number($("#ReportHtEvaluation0Wert3").val());

		if(wert1){x++;}
		if(wert2){x++;}
		if(wert3){x++;}

		var total = (wert1 + wert2 + wert3) / x;
		$("#ReportHtEvaluation0WertTotal").val(Math.round(total)).change();
		$("#ReportHtEvaluation0HaerteHv").val(Math.round(total)).change();
	});
	});
	</script>
	';
	return $output;
	}

	public function LeftMenueHeight($reportnumber=null) {
	$text_close_report = __('Warning, this function may not be reversed!', true);
	$text_duplicat_report = __('Are you sure you want to duplicate this Report?', true);
	$text_duplicat_evalutionarea = __('Are you sure you want to duplicate this Evalution Area?', true);
	$text_duplicat_weld = __('Are you sure you want to duplicate this weld?', true);
	$text_delete_report = __('Are you sure you want to delete this Report?', true);
	$text_delete_order = __('Are you sure you want to delete this Order?', true);
	$text_delete_evalutionarea = __('Are you sure you want to delete this Evalution Area?', true);
	$text_delete_generally = __('Are you sure you want to delete this entry?', true);
	$text_delete_file = __('Are you sure you want to delete this file?', true);

	$output  = null;
	$output .= $this->GlobalErrorHandler();
	$output .= '<script type="text/javascript">
				$(document).ready(function(){

/*
						var History = window.History;

						if (History.enabled) {
							State = History.getState();
							// set initial state to first page that was loaded
							History.pushState({urlPath: window.location.pathname}, $("title").text(), State.urlPath);
						}
						else {
							return false;
						}
*/


						$("#container").css("background-image","none");
						$("#top-menue").css("height",$("#content").height()+"px");

						$(".message_wrapper").css("position","fixed");
						$(".message_wrapper").css("z-index","2000");
						$(".message_wrapper").css("width","500px");
						$(".message_wrapper").css("height","4em");
						$(".message_wrapper").css("left","50%");
						$(".message_wrapper").css("margin-left","-250px");
						$(".message_wrapper").css("top","50%");
						$(".message_wrapper").css("margin-top","-2em");

						setTimeout( function(){$(".message_wrapper").hide("slow");} , 5000);

						$("div.checkbox input").button();
						$("div.radio:not(.ui-buttonset)").buttonset();

						$("#evalutionback").click(function() {
							$("#container").load($(this).attr("formaction"), {"ajax_true": 1});
						});

						$(".hidden").click(function() {
							$("#testdeviceforms").load($(this).attr("href"), {"ajax_true": 1});
							return false;
						});

						$("#maximizethismodal").click(function() {
							$(".ui-dialog").show();
							$("#maximizethismodal").hide();
							return false;
						});

						$("#_infos_link").click(function() {
							if($(".hide_infos_div").is(":visible")){
								$(".hide_infos_div").slideUp("slow", function() {
									$("#_infos_link").removeClass("icon_hide_infos");
									$("#_infos_link").addClass("icon_show_infos");
									$("#_infos_link").attr("title","'. __('Show infos',true) .'");
									$("#_infos_link").text("'. __('Show infos',true) .'");
								});
							} else {
								$(".hide_infos_div").slideDown("slow", function() {
									$("#_infos_link").removeClass("icon_show_infos");
									$("#_infos_link").addClass("icon_hide_infos");
									$("#_infos_link").attr("title","'. __('Hide infos',true) .'");
									$("#_infos_link").text("'. __('Hide infos',true) .'");
								});
							}
							return false;
						});

						// Test ob geänderte Formulardaten gespeichert wurden
						var SaveQuest = 0;

						$("div.detail input, div.detail select, div.detail textarea").change(function() {
//							SaveQuest = 1;
						});

						$("#container a.ajax, #container div.breadcrumbs a, #container td a.ajax, #container th a.ajax, #container .paging a").click(function(ev) {



							if(SaveQuest == 1){
								checkDuplicate = confirm("Ihre geänderten Daten wurden noch nicht gespeichert.\nKlicken Sie auf Ok um diese Seite zu verlassen, ohne Ihre Daten zu speichern");

								if (checkDuplicate == false) {
									return false;
								}
							}

							// eine Ausnahme für den Dateidownload
							if($(this).attr("class") == "filelink"){
								return;
							}
							if($(this).attr("class") == "round modal"){
								return;
							}
							if($(this).attr("class") == "left modal"){
								return;
							}
							if($(this).attr("class") == "right modal"){
								return;
							}
							if($(this).attr("class") == "round modal deleteadditional"){
								return;
							}

							if($(this).attr("class") == "right ajax delete_generally") {
								checkDuplicate = confirm("'.$text_delete_generally.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("id") == "delete_order") {
								checkDuplicate = confirm("'.$text_delete_order.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("id") == "dublicate_report") {
								checkDuplicate = confirm("'.$text_duplicat_report.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("id") == "dublicate_evalutionarea") {
								checkDuplicate = confirm("'.$text_duplicat_evalutionarea.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("id") == "dublicate_weld") {
								checkDuplicate = confirm("'.$text_duplicat_weld.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("id") == "delete_evalution") {
								checkDuplicate = confirm("'.$text_delete_evalutionarea.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("class") == "filelinkdel") {
								checkDuplicate = confirm("'.$text_delete_file.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("id") == "text_delete_report") {
								checkDuplicate = confirm("'.$text_delete_report.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("id") == "text_delete_project") {
								checkDuplicate = confirm("'.$text_delete_report.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if(!ev.ctrlKey) {

								$("#container").empty();
								$("#container").css("background-image","url(img/indicator_bg_blue.gif)");
								$("#container").css("background-repeat","no-repeat");
								$("#container").css("background-position","center center");
								$("#container").css("min-height","4em");

								if($(this).hasClass("assignlink")) {
									$("#container").load($(this).attr("href"), { "ajax_true": 1, "type": "assign" });
								} else if($(this).hasClass("repairreport")) {
									$("#container").load($(this).attr("href"), {"ajax_true": 1, "data[Reportnumber][Generally]": 1, "data[Reportnumber][Specific]": 1, "data[Reportnumber][Evaluation]": 1, "data[repair]": 1, "data[repair_status]": 1});
								} else {
									$("#container").load($(this).attr("href"), {"ajax_true": 1});
								}
							} else {
								window.location.href=$(this).attr("href");
							}

							return false;
						});

						var modalheight = Math.ceil(($(window).height() * 90) / 100);
						var modalwidth = Math.ceil(($(window).width() * 90) / 100);

						var dialogOpts = {
							modal: false,
							width: modalwidth,
							height: modalheight,
							autoOpen: false,
							draggable: true,
							resizeable: true,
							create: function(event,ui){
									$("#dialog").empty();
									$("#dialog").css("background-image","url(img/indicator.gif)");
									$("#dialog").css("background-repeat","no-repeat");
									$("#dialog").css("background-position","center center");
								},
							close: function(event,ui){
									$("#dialog").empty();
								}
							};

						var dialogmediumOpts = {
							modal: true,
							width: 700,
							height: 500,
							autoOpen: false,
							draggable: true,
							resizeable: true,
							close: function(event,ui){
									$("#dialog").empty();
								}
							};

						var dialogsmallOpts = {
							modal: true,
							width: 350,
							height: 300,
							autoOpen: false,
							draggable: true,
							resizeable: true,
							close: function(event,ui){
									$("#dialog").empty();
								}
							};

						$("#evalationnew").click(function() {
							if($(this).parents("form").length != 0 && $(this).parents(".modalarea").length == 0)
							{
								var formtarget = $(this).attr("formtarget");
								var data = $(this).parents("form").serializeArray();
								data.push({name: "ajax_true", value: 1});

								var url = $(this).parents("form").attr("action");
								$.ajax({
									url: url,
									data: data,
									type: "POST",
									success: function(data) {
										$("#container").html(data);
										$("#container").load(formtarget, {"ajax_true": 1});
									}
								});
							}
							else {
								$("#container").load($(this).attr("formtarget"), {"ajax_true": 1});
							}
							return false;
						});

						$("a.modal:not(.specialchars), div.images ul li a, button.modal").click(function() {

							if($("#dialog").hasClass("ui-dialog-content")) {
								$("#dialog").dialog("destroy");
							}

							$("#maximizethismodal").hide();

							if($(this).parents("form").length != 0 && $(this).parents(".modalarea").length == 0)
							{
//								$(this).parents("form").submit();
							}

							if($(this).attr("class") == "round modal deleteadditional") {
								checkDuplicate = confirm("'.$text_delete_generally.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("class") == "right delete_generally modal") {
								checkDuplicate = confirm("'.$text_delete_generally.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							if($(this).attr("id") == "text_delete_project") {
								checkDuplicate = confirm("'.$text_delete_generally.'");
								if (checkDuplicate == false) {
									return false;
								}
							}

							$("#dialog").dialog(dialogOpts);

							if($(this).hasClass("assignlink") || $(this).hasClass("assign")) {
								$("#dialog").load($(this).attr("href"), {
									"ajax_true": 1,
									"type": "assign"
								});
							} else if($(this).hasClass("statistik")) {
								$("#dialog").load($(this).attr("href"), {
									"ajax_true": 1,
									"type": "statistics"
								});
							} else if($(this).attr("class") == "modal dropdown"){
								if(test = $(this).siblings("input[type=\'text\'],select,textarea").attr("name").match(/data\[(.*?)\](\[0\])*\[(.*?)\]/)) {
									$("#dialog").load($(this).attr("href"), {
										"ajax_true": 1,
										"dropdownid": test[1]+"0"+test[3]
										}
									)
								}
							} else if($(this).attr("class") == "modal assign") {
								$("#dialog").load($(this).attr("href"), {
									"ajax_true": 1,
									"id": "'.(isset($reportnumber['Reportnumber']) ? $reportnumber['Reportnumber']['id'] : '').'"
								})
							} else if($(this).hasClass("print")) {
								prevent = $(this).data("prevent");
								if($(this).attr("href").match(/\/pdf\//) && prevent == 1) return false;
								$("#dialog").load($(this).attr("href"), {
									"ajax_true": 1,
									"prevent": prevent
								})
							} else if($(this).hasClass("repairreport")) {
								$("#dialog").load($(this).attr("href"), {
									"ajax_true": 1,
									"data[Reportnumber][Generally]": 1,
									"data[Reportnumber][Specific]": 1,
									"data[Reportnumber][Evaluation]": 1,
									"data[repair]": 1,
									"data[repair_status]": 1
								})
							} else {
								data = {"ajax_true": 1};
								if($(this).attr("rel")) $.extend(data, {"type": $(this).attr("rel")});
								$("#dialog").load($(this).attr("href"), data);
							}

							$("#dialog").dialog("open");
							$(".ui-dialog").show();
							return false;
						});

						$("button#nahtassistent").click(function() {
							$("#dialog").dialog(dialogOpts);

								$("#dialog").load($(this).attr("formtarget"), {"ajax_true": 1})

							$("#dialog").dialog("open");
							return false;
						});

/*
						$("li.image a").click(function() {
							alert("test");
							$("#dialog").dialog(dialogmediumOpts);
							$("#dialog").load($(this).attr("href"), {"ajax_true": 1})
							$("#dialog").dialog("open");
							return false;
						});
*/
						$("a.modalsmall").click(function() {
							$("#dialog").dialog(dialogsmallOpts);
							$("#dialog").load($(this).attr("href"), {"ajax_true": 1})
							$("#dialog").dialog("open");
							return false;
						});

						$("button#back").click(function() {
							$("#container").load($(this).val(), {"ajax_true": 1});
							return false;
						});

						// allgemeines Formular
						$("div.inhalt form:not(#weldassistentWeldassistentForm), div.detail form").bind("submit", function() {
							var btn = $(this).find("input[type=submit]:focus" );
							var data = $(this).serializeArray();
							data.push({name: "ajax_true", value: 1});
							data.push({name: "forwardaction", value: btn.attr("src")});
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

						$("[disabled]:not([rel=\'saving\'])").off().unbind().click(function(e) {return false;});
						$("a[disabled]").fadeTo(0, 0.5);
					});
				</script>';
	return $output;
	}

	public function ModalFunctions($reportnumber=null) {

	$text_close_report = __('Warning, this function may not be reversed!', true);
	$text_duplicat_report = __('Are you sure you want to duplicate this Report?', true);
	$text_duplicat_evalutionarea = __('Are you sure you want to duplicate this Evalution Area?', true);
	$text_duplicat_weld = __('Are you sure you want to duplicate this weld?', true);
	$text_delete_report = __('Are you sure you want to delete this Report?', true);
	$text_delete_order = __('Are you sure you want to delete this Order?', true);
	$text_delete_evalutionarea = __('Are you sure you want to delete this Evalution Area?', true);
	$text_delete_generally = __('Are you sure you want to delete this entry?', true);
	$text_delete_file = __('Are you sure you want to delete this file?', true);

	$output  = null;

	$output .= $this->GlobalErrorHandler();
	$output .= '
	<script type="text/javascript">
	$(document).ready(function(){

		$("#dialog").scrollTop("0");
		$("#dialog div.checkbox input").button();
		$("a[disabled]").click(function(e) {return false;});

		$("a#minimizethismodal").click(function() {
			$(".ui-dialog").hide();
			$("#maximizethismodal").show();
			$("a#maximizethismodal").attr("title",$("a#maximizethismodal").text() + " - " + $("div#dialog h2").text());
			return false;
		});

		$("div.modalarea a.ajax").click(function() {

			data = { "ajax_true": 1 };

			if($(this).hasClass("assignlink")) data = $.extend(data, { "linked": "1" });

//			if($(this).data()) data = $.extend(data, $(this).data());

			if($(this).data()) {

				_data = $(this).data();

				for(idx in _data) {
					if(idx.substr(0, 2)== "ui") continue;
					if(idx == "tooltip") continue;
					$.extend(data, JSON.parse(\'{"\'+idx+\'": "\'+_data[idx]+\'"}\'));
				}
			}

			$("#container").empty();
			$("#container").css("background-image","url(img/indicator_bg_blue.gif)");
			$("#container").css("background-repeat","no-repeat");
			$("#container").css("background-position","center center");
			$("#container").css("min-height","4em");
			$("#container").load($(this).attr("href"), data);

			if($(".ui-dialog").is(":visible")){
				$("#dialog").dialog().dialog("close");
			} else {

			}

			return false;

		});

		var modalheight = Math.ceil(($(window).height() * 90) / 100);
		var modalwidth = Math.ceil(($(window).width() * 90) / 100);

//		$("div.checkbox input").button();
//		$("div.radio").buttonset();

		$("div.checkbox input:not(.no-transform)").button();
		$("div.radio:not(.ui-buttonset)").buttonset();

		var dialogOpts = {
			modal: false,
			width: modalwidth,
			height: modalheight,
			autoOpen: false,
			draggable: true,
			resizeable: true
		};

		$("a.mymodal, div.mymodal a, .modalarea .paging a, fieldset.mymodal a").off("click").click(function() {

			$("#dialog").empty();
			$("#dialog").css("background-image","url(img/indicator.gif)");
			$("#dialog").css("background-repeat","no-repeat");
			$("#dialog").css("background-position","center center");

			$("#dialog").dialog("destroy");
			formtarget = $(this).data("formtarget");

			if($(formtarget).length == 0) formtarget = "#dialog";

			if($(this).attr("id") == "closethismodal") {
				$("#dialog").empty();
				$("#dialog").dialog().dialog("close");
				return false;
			}

			if($(this).attr("id") == "mass_action_okay") {
				var data = $("#fake").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});

			';

$output .= 'data.push({name: "data[Reportnumber][okay]", value: 1});';

			if(isset($this->request->data['Reportnumber'])) {
				foreach($this->request->data['Reportnumber'] as $_key => $_reportnumber){
					$output .= 'data.push({name: "data[Reportnumber]['.$_key.']", value: "'.$_reportnumber.'"});';
				}
			}

$url = Router::url(array_merge(array('controller' => 'reportnumbers','action' => 'massActions'),$this->request->projectvars['VarsArray']));

$output .= '$.ajax({
				type	: "POST",
				cache	: true,
				url		: "'.$url.'",
				data	: data,
				success: function(data) {
					$("#dialog").html(data);
					$("#dialog").show();
				}
			});

			$("#dialog").dialog("open");
				return false;

		}

		if($(this).attr("class") == "right delete_generally mymodal") {
			checkDuplicate = confirm("'.$text_delete_generally.'");
			if (checkDuplicate == false) {
				return false;
			}
		}

		$("#dialog").dialog(dialogOpts);

		if(this.hasAttribute("download")) data = {"filename": $(this).attr("download").trim()};
		else data = {"ajax_true": 1};

		if($(this).parents(".paging").length != 0) {
			data = [];
		';

		if(isset($this->request->data)) {
			foreach(Hash::flatten($this->request->data) as $key=>$val) {
				if($key == 'Topproject.id') break;

				$output .= 'data.push({name: "data['.str_replace('.','][', $key).']", value: "'.(is_array($val) && empty($val) ? '' : str_replace('"','\'', str_replace('\\','\\\\', $val))).'"});'.PHP_EOL;
			}
		}

		$output .= '
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").show();
			}
		});

		$("#dialog").dialog("open");

		return false;

		} else

		if($(this).hasClass("dropdown")) {
			test = $(this).siblings("input[type=\'text\'],select,textarea").first().attr("name").match(/data\[(.*?)\](\[0\])*\[(.*?)\]/);
							$.extend(data, {dropdownid:  test[1]+"0"+test[3]});
							$.extend(data, {backlink: 1});
						} else if($(this).hasClass("dependency")) {
							$.extend(data, {backlink: 1});
						} else if($(this).hasClass("assignlink")) {
							$.extend(data, { "ajax_true": 1, "type": "assign" });
						} else if($(this).hasClass("addrepairlink")) {
							$.extend(data, { "data[Reportnumber][Generally]": 1, "data[Reportnumber][Specific]": 1, "data[Reportnumber][Evaluation]": 1, "data[repair]": 1 });
							formtarget = "#container";
						} else {
							if(this.hasAttribute("rel")) {
								if(test = $(this).attr("rel").toLowerCase().match(/^(set|remove)(parent|child)\[([0-9]+)\]$/i)) {
									$.extend(data, {type:test[2], id:test[3]});
								} else {
									$.extend(data, {type: $(this).attr("rel").trim()});
								}
							}
						}

//						if($(this).data()) $.extend(data, $(this).data());
						if($(this).data()) {
							_data = $(this).data();

							for(idx in _data) {
								if(idx.substr(0, 2)== "ui") continue;
								if(idx == "tooltip") continue;


								$.extend(data, JSON.parse(\'{"\'+idx+\'": "\'+_data[idx]+\'"}\'));
//								$.extend(data, {idx: _data[idx]});
							}
						}

//						$("#dialog").empty().load($(this).attr("href"), data);

						$(formtarget).load($(this).attr("href"), data);
						if(formtarget == "#dialog") $("#dialog").dialog().dialog("open");
						else $("#dialog").dialog().dialog("close");

						return false;
					});


					$("form.dialogform").bind("submit", function() {

						var data = $(this).serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});

						$("#dialog").empty();
						$("#dialog").css("background-image","url(img/indicator.gif)");
						$("#dialog").css("background-repeat","no-repeat");
						$("#dialog").css("background-position","center center");

						$.ajax({
								type	: "POST",
								cache	: false,
								url		: $(this).attr("action"),
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
						});


					$("form.Searchform").bind("submit", function() {

						var data = $(this).serializeArray();
						data.push({name: "ajax_true", value: 1});

						$.ajax({
								type	: "POST",
								cache	: false,
								url		: $(this).attr("action"),
								data	: data,
								success: function(data) {
		    						$("#container").html(data);
		    						$("#container").show();
								}
							});

							$("#dialog").dialog();
							if ($("#dialog").dialog("isOpen") === true) {
								$("#dialog").dialog("close");
							}

							return false;
						});
					});

					$("form.GlobalSearchform").bind("submit", function() {

						$("#dialog").empty();
						$("#dialog").css("background-image","url(img/indicator.gif)");
						$("#dialog").css("background-repeat","no-repeat");
						$("#dialog").css("background-position","center center");

						var data = $(this).serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "searchglobal", value: 1});

						$.ajax({
								type	: "POST",
								cache	: false,
								url		: $(this).attr("action"),
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});

							return false;
					});

					$("form.Searchform :submit:not(:disabled).list").bind("click", function(e) {
						var data = $(this).parents("form").serializeArray();

						data.push({name: "ajax_true", value: 1});
						data.push({name: "showresult", value: 1});
						data.push({name: "id", value: "'.(isset($reportnumber['Reportnumber']) && is_array($reportnumber['Reportnumber']) ? $reportnumber['Reportnumber']['id'] : '').'"});

						$.ajax({
							type	: "POST",
							cache	: false,
							url		: $(this).parents("form").attr("action"),
							data	: data,
							success: function(data) {
	    						$("#dialog").html(data);
	    						$("#dialog").show();
							}
						});

						return false;
					});

					$("form.Searchform button.setParent").bind("click", function(e) {
						$.ajax({
							type	: "POST",
							cache	: false,
							url		: $(this).attr("href"),
							data	: {"ajax_true": 1},
							success: function(data) {
								$("#dialog").dialog();
	    						if ($("#dialog").dialog("isOpen") === true) {
									$("#dialog").dialog("close");
								}

								$("#container").html(data);
							}
						});

						return false;
					});

					$("form.Searchform button.setParent").bind("click", function(e) {
						$.ajax({
							type	: "POST",
							cache	: false,
							url		: $(this).attr("href"),
							data	: {"ajax_true": 1},
							success: function(data) {
								$("#dialog").dialog();
	    						if ($("#dialog").dialog("isOpen") === true) {
									$("#dialog").dialog("close");
								}

								$("#container").html(data);
							}
						});

						return false;
					});

					$("form.logoUpload").bind("submit", function() {

						var data = $(this).serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});
						data.push({name: "image", value: files[0]});

						$.ajax({
								type	: "POST",
								cache	: false,
								url		: $(this).attr("action"),
								data	: data,
								mimeType: "multipart/form-data",
								processData: false,
								contentType: false,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
						});
						return false;


	});

	$("[disabled]:not([rel=\'saving\']").off().unbind().click(function(e) {return false;});
	$("a[disabled]").fadeTo(0, 0.5);
	</script>';

	return $output;
	}

	public function ShowAccordion() {

	$output  = null;
	$output = '
	<script>
	$(function() {

		$(".accordion").accordion({
			heightStyle: "content"
			});

		var OutputHeight = 0;
		$("p.output").each(function(){
				if($(this).height() > OutputHeight) {
					OutputHeight = $(this).height();
				}
			});
		$("p.output").css("height",OutputHeight+"px");

	});
	</script>
	';

	return $output;
	}

	public function EditMenue($editmenue) {
		if($editmenue == null){return;}

		$output  = null;

		return $output;
	}

	public function EditEvalutionMenue($editmenue) {
		if($editmenue == null){return;}

		$output  = null;

		$output .= '
		<script type="text/javascript">
		$(document).ready(function(){

			var SaveQuest = 0;

			$("form.editreport input,form.editreport select,form.editreport textarea").change(function() {
				SaveQuest = 1;
			});

			$("ul.editmenue a").click(function() {
/*
				if(SaveQuest == 1){
					checkDuplicate = confirm("Ihre geänderten Daten wurden noch nicht gespeichert.\nKlicken Sie auf Ok um diese Seite zu verlassen, ohne Ihre Daten zu speichern");

					if (checkDuplicate == false) {
						return false;
					}
				}
*/
				var data = $("#fakeform").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "rel_menue", value: $(this).attr("rel")});

				$.ajax({
						type	: "POST",
						cache	: false,
						url		: $(this).attr("href"),
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
		';

		return $output;
	}

	public function SessionFormData($form) {
		$output  = null;

		$output .= '
		<script type="text/javascript">
			$(document).ready(function(){

   				$(".dropdown").click(function(){
                var data = $("'.$form.'").serializeArray();
                data.push({name: "ajax_true", value: 1});
                data.push({name: "req_action", value: "'.$this->request->action.'"});
                data.push({name: "req_controller", value: "'.$this->request->controller.'"});


                $.ajax({
					type	: "POST",
					cache	: false,
					url     : "'.$this->Html->Url(array('action' => 'sessionstorage')).'",
					data	: data

				});
                return false;

   			});
		});
		</script> ';
		return $output;
	}
}

?>
