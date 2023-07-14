<?php
class NavigationHelper extends AppHelper {

	var $helpers = array('Html','Form','Ajax','Javascript','Navigation');

	public function PathToCSS($css) {

		if(Configure::check('PathToCSS')){
			if(file_exists(WWW_ROOT . Configure::read('PathToCSS') . $css . '.css')){
				$output = Configure::read('PathToCSS');
				return $output;
			} else {
				return null;
			}
		}
		return null;
	}

	public function PathToSpecificCSS() {

		if(Configure::check('SpecialCss') == false) return;
		$SpecialCss = Configure::read('SpecialCss');

		if(file_exists(WWW_ROOT . 'individual_css' . DS . $SpecialCss . DS . 'specific.css')){
			$CssPath = '../individual_css' . '/' . $SpecialCss . '/' . 'specific.css';
			return $this->Html->css($CssPath);
		}
	}

	public function quickSearching($action,$minLength,$discription) {
		$output = null;
		$output .= $this->Form->create('Quicksearch',array('id' => 'QuicksearchForm','class' => 'quip_search_form'));
		$output .= $this->Form->input('hidden', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => 1,
						)
					);

		$output .= $this->Form->input('this_id', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => 0,
						)
					);

		$output .= $this->Form->input('searching_autocomplet', array(
						'class' =>'autocompletion searching_autocomplet',
						'label' => false,
						'div' => false,
						'title' => $discription,
						'placeholder' => $discription,
						'formaction' => 'autocomplete'
						)
					);

		$output .= $this->Form->end();

		$output .= '
					<script>
						$(function() {

							$("form#QuicksearchForm").on("keyup keypress", function(e) {
								var keyCode = e.keyCode || e.which;
								if(keyCode === 13) {
									e.preventDefault();
									return false;
								}
							});

							var placehoder_id = "#QuicksearchSearchingAutocomplet";
							if(!("placeholder" in document.createElement("input"))) {
								if($(placehoder_id).val() == "") {
									$(placehoder_id).css("color","#999");
									$(placehoder_id).val($(placehoder_id).attr("placeholder"));
								}
								$(placehoder_id).click(function() {
									if($(placehoder_id).val() == $(placehoder_id).attr("placeholder")) {
										$(placehoder_id).css("color","#000");
										$(placehoder_id).val("");
									}
								});
								$(placehoder_id).blur(function() {
									if($(placehoder_id).val() == "") {
										$(placehoder_id).css("color","#999");
										$(placehoder_id).val($(placehoder_id).attr("placeholder"));
									}
								});
							};

							var i = 0;
							$("#QuicksearchForm input.autocompletion").each(function(key,value){

								$(this).autocomplete({
									minLength: '.$minLength.',
									delay: 4,
									source: function(request,response) {
										$.ajax({
											url: "'.$this->Html->url(array_merge(array('action' => $action),$this->request->projectvars['VarsArray'])).'",
											dataType: "json",
											data: {
												term : request.term,
											},

									success: function(data) {
										response(data);
										},
									});
									},
									select: function(event,ui) {

										$("#QuicksearchSearchingAutocomplet").val(ui.item.value);
										$("#QuicksearchThisId").val(ui.item.key);

										var data = $("#QuicksearchForm").serializeArray();
										data.push({name: "ajax_true", value: 1});
										data.push({name: "this_id", value: ui.item.key});

										$.ajax({
											type	: "POST",
											cache	: false,
											url		: $("#QuicksearchForm").attr("action"),
											data	: data,
											success: function(data) {
		    								$("#container").html(data);
		    								$("#container").show();
											}
										});
										return false;

									}
								});
								i++;
							});

							$("#QuicksearchSearchingAutocomplet").change(function() {
								if($("#QuicksearchSearchingAutocomplet").val() == "" && $("#QuicksearchThisId").val() == 0){
									$("#container").load($("#QuicksearchForm").attr("action"), {"ajax_true": 1});
								}
							});

							$("form#QuicksearchForm").bind("submit", function() {
								if($("#this_id").val() == 0){
									return false;
								}

								var data = $(this).serializeArray();
								data.push({name: "ajax_true", value: 1});
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
						});
					</script>';

		return $output;
	}
/*
	public function quickExaminerSearching($action,$minLength,$discription,$showsummary) {

		$targetcontroller = 'examiners';
		$targetation = 'index';
		$url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>$targetation));
		$summery_url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>'summary'));

		$output = null;
		$output .= $this->Form->create('QuickExaminersearch',array('id' => 'QuickExaminersearchForm','class' => 'quip_search_form'));
		$output .= $this->Form->input('hidden', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => 1,
						)
					);

		$output .= $this->Form->input('searching_autocomplet', array(
						'class' =>'autocompletion searching_autocomplet',
						'label' => false,
						'div' => false,
						'title' => $discription,
						'placeholder' => $discription,
						'formaction' => 'autocomplete'
						)
					);

		$output .= $this->Form->end();

		$output .= '<div id="summary_output" class="summary_output"></div>';

		$output .= '
					<script>
						$(function() {
							';

		if($showsummary == true){
			$output .= '
							$("#summary_output").load("'.$summery_url .'", {"ajax_true": 1});
							';
		}

		$output .= '
							$("form#QuickExaminersearchForm").on("keyup keypress", function(e) {
								var keyCode = e.keyCode || e.which;
								if(keyCode === 13) {
									e.preventDefault();
									return false;
								}
							});

							var placehoder_id = "#QuickExaminersearchSearchingAutocomplet";
							if(!("placeholder" in document.createElement("input"))) {
								if($(placehoder_id).val() == "") {
									$(placehoder_id).css("color","#999");
									$(placehoder_id).val($(placehoder_id).attr("placeholder"));
								}
								$(placehoder_id).click(function() {
									if($(placehoder_id).val() == $(placehoder_id).attr("placeholder")) {
										$(placehoder_id).css("color","#000");
										$(placehoder_id).val("");
									}
								});
								$(placehoder_id).blur(function() {
									if($(placehoder_id).val() == "") {
										$(placehoder_id).css("color","#999");
										$(placehoder_id).val($(placehoder_id).attr("placeholder"));
									}
								});
							};

							var i = 0;
							$("#QuickExaminersearchForm input.autocompletion").each(function(key,value){

								$(this).autocomplete({
									minLength: '.$minLength.',
									delay: 4,
									source: function(request,response) {
										$.ajax({
											url: "'.$this->Html->url(array('controller' => 'examiners','action' => $action)).'",
											dataType: "json",
											data: {
												term : request.term,
												targetcontroller : "'.$targetcontroller.'",
												targetation : "'.$targetation.'",
											},

									success: function(data) {
										response(data);
										},
									});
									},
									select: function(event,ui) {

										$("#QuickExaminersearchSearchingAutocomplet").val(ui.item.value);

										var data = $("#QuickExaminersearchForm").serializeArray();
										data.push({name: "ajax_true", value: 1});
										data.push({name: "examiner_id", value: ui.item.key});

										$.ajax({
											type	: "POST",
											cache	: false,
											url		: "' . $url . '",
											data	: data,
											success: function(data) {
		    								$("#dialog").html(data);
		    								$("#dialog").show();
											}
										});
										return false;

									}
								});
								i++;
							});

							$("#QuickExaminersearchSearchingAutocomplet").change(function() {
								if($("#QuickExaminersearchSearchingAutocomplet").val() == ""){
									$("#dialog").load($("#QuickExaminersearchForm").attr("action"), {"ajax_true": 1});
								}
							});
						});

					</script>';

		return $output;
	}
*/
	public function quickComponentSearching($action,$ControllerArray,$showsummary) {
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

		$url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>$targetation));
		$clear_url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>'index'));
		$summery_url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>'summary'));

		$output = null;
		$output .= $this->Form->create($Quicksearch,array('id' => $QuicksearchForm,'class' => 'quip_search_form'));
		$output .= $this->Form->input('hidden', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => 1,
						)
					);

		$output .= $this->Form->input('searching_autocomplet', array(
						'class' =>'autocompletion searching_autocomplet',
						'label' => false,
						'div' => false,
						'title' => $description,
						'placeholder' => $description,
						'formaction' => 'autocomplete'
						)
					);

		$output .= $this->Form->end();

		$output .= '<div id="summary_output" class="summary_output">';


		if($showsummary == true){
			$output .= $this->Html->link(__('Send this vision test informations per email',true),array_merge(array('action' => 'email_eyecheck'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_email_eyecheck','title' => __('Send this vision test informations per email',true)));
			$output .= $this->Html->link(__('Send this certificate informations per email',true),array_merge(array('action' => 'email_certificate'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_email_certificate','title' => __('Send this certificate informations per email',true)));
			$output .= $this->Html->link(__('Show detail informations',true), array_merge(array('controller'=>$targetcontroller,'action'=>'summary'), array()), array('id' => '_show_summary', 'class' => '_show_summary modal icon icon_summary_infos','title' => __('Show detail informations',true)));
		}

		$output .= '</div>';

		$output .= '
					<script>
						$(function() {
							';

		if($showsummary == true){
			$output .= '

							$(".show_summary").click(function() {
								$("a.show_summary").hide();
								$("div.summary_output").css("background-image","url(img/indicator.gif)");
								$("div.summary_output").css("background-repeat","no-repeat");
								$("div.summary_output").css("background-position","left bottom");
								$("div.summary_output").css("background-size","auto 90%");
								$("#summary_output").load($(this).attr("href"), {"ajax_true": 1});
								return false;
							});

							';
		}

		$output .= '
							$("form#'.$QuicksearchForm .'").on("keyup keypress", function(e) {
								var keyCode = e.keyCode || e.which;
								if(keyCode === 13) {
									e.preventDefault();
									return false;
								}
							});

							var placehoder_id = "#'.$QuicksearchSearchingAutocomplet.'";
							if(!("placeholder" in document.createElement("input"))) {
								if($(placehoder_id).val() == "") {
									$(placehoder_id).css("color","#999");
									$(placehoder_id).val($(placehoder_id).attr("placeholder"));
								}
								$(placehoder_id).click(function() {
									if($(placehoder_id).val() == $(placehoder_id).attr("placeholder")) {
										$(placehoder_id).css("color","#000");
										$(placehoder_id).val("");
									}
								});
								$(placehoder_id).blur(function() {
									if($(placehoder_id).val() == "") {
										$(placehoder_id).css("color","#999");
										$(placehoder_id).val($(placehoder_id).attr("placeholder"));
									}
								});
							};

							var i = 0;
							$("#'.$QuicksearchForm.' input.autocompletion").each(function(key,value){

								$(this).autocomplete({
									minLength: '.$minLength.',
									delay: 4,
									source: function(request,response) {

										var data = [];

										data.push({name: "term", value: request.term});
										data.push({name: "targetcontroller", value: "'.$targetcontroller.'"});
										data.push({name: "targetation", value: "'.$targetation.'"});
										data.push({name: "model", value: "'.$ControllerArray['Model'].'"});
										data.push({name: "field", value: "'.$ControllerArray['Field'].'"});

		';
										if(isset($ControllerArray['Conditions']) && is_array($ControllerArray['Conditions'])){
											foreach($ControllerArray['Conditions'] as $_key => $_conditions){
												$output .= 'data.push({name: "data[Conditions]['.$_key.']", value: "'.$_conditions.'"});';
											}
										}

		$output .= '

										$.ajax({
											url: "'.$this->Html->url(array('controller' => $targetcontroller,'action' => $action)).'",
											dataType: "json",
											data: data,

									success: function(data) {
										response(data);
										},
									});
									},
									select: function(event,ui) {

										$("#'.$QuicksearchSearchingAutocomplet.'").val(ui.item.value);

										var data = $("#'.$QuicksearchForm.'").serializeArray();
										data.push({name: "ajax_true", value: 1});
										data.push({name: "'.$target_id.'", value: ui.item.key});

										$.ajax({
											type	: "POST",
											cache	: false,
											url		: "' . $url . '",
											data	: data,
											success: function(data) {
		    								$("#container").html(data);
		    								$("#container").show();
											}
										});
										return false;

									}
								});
								i++;
							});

							$("#'.$QuicksearchSearchingAutocomplet.'").change(function() {
								if($("#'.$QuicksearchSearchingAutocomplet.'").val() == ""){
									$("#container").load($("#'.$QuicksearchForm.'").attr("action"), {"ajax_true": 1});
								}
							});
						});

					</script>';

		return $output;
	}

	public function quickComponentSearchingModal($action,$ControllerArray,$showsummary) {

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

		$url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>$targetation));
		$clear_url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>'index'));
		$summery_url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>'summary'));

		$output = null;
		$output .= $this->Form->create($Quicksearch,array('id' => $QuicksearchForm,'class' => 'quip_search_form'));
		$output .= $this->Form->input('hidden', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => 1,
						)
					);

		$output .= $this->Form->input('searching_autocomplet', array(
						'class' =>'autocompletion searching_autocomplet',
						'label' => false,
						'div' => false,
						'title' => $description,
						'placeholder' => $description,
						'formaction' => 'autocomplete'
						)
					);

		$output .= $this->Form->end();

		$output .= '<div id="summary_output" class="summary_output">';


		if($showsummary == true){
			$output .= $this->Html->link(__('Send this vision test informations per email',true),array_merge(array('action' => 'email_eyecheck'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_email_eyecheck','title' => __('Send this vision test informations per email',true)));
			$output .= $this->Html->link(__('Send this certificate informations per email',true),array_merge(array('action' => 'email_certificate'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_email_certificate','title' => __('Send this certificate informations per email',true)));
			$output .= $this->Html->link(__('Show detail informations',true), array_merge(array('controller'=>$targetcontroller,'action'=>'summary'), array()), array('id' => '_show_summary', 'class' => '_show_summary modal icon icon_summary_infos','title' => __('Show detail informations',true)));
		}

		$output .= '</div>';

		$output .= '
					<script>
						$(function() {
							';

		if($showsummary == true){
			$output .= '

							$(".show_summary").click(function() {
								$("a.show_summary").hide();
								$("div.summary_output").css("background-image","url(img/indicator.gif)");
								$("div.summary_output").css("background-repeat","no-repeat");
								$("div.summary_output").css("background-position","left bottom");
								$("div.summary_output").css("background-size","auto 90%");
								$("#summary_output").load($(this).attr("href"), {"ajax_true": 1});
								return false;
							});

							';
		}

		$output .= '
							$("form#'.$QuicksearchForm .'").on("keyup keypress", function(e) {
								var keyCode = e.keyCode || e.which;
								if(keyCode === 13) {
									e.preventDefault();
									return false;
								}
							});

							var placehoder_id = "#'.$QuicksearchSearchingAutocomplet.'";
							if(!("placeholder" in document.createElement("input"))) {
								if($(placehoder_id).val() == "") {
									$(placehoder_id).css("color","#999");
									$(placehoder_id).val($(placehoder_id).attr("placeholder"));
								}
								$(placehoder_id).click(function() {
									if($(placehoder_id).val() == $(placehoder_id).attr("placeholder")) {
										$(placehoder_id).css("color","#000");
										$(placehoder_id).val("");
									}
								});
								$(placehoder_id).blur(function() {
									if($(placehoder_id).val() == "") {
										$(placehoder_id).css("color","#999");
										$(placehoder_id).val($(placehoder_id).attr("placeholder"));
									}
								});
							};

							var i = 0;
							$("#'.$QuicksearchForm.' input.autocompletion").each(function(key,value){

								$(this).autocomplete({
									minLength: '.$minLength.',
									delay: 4,
									source: function(request,response) {

										var data = [];

										data.push({name: "term", value: request.term});
										data.push({name: "targetcontroller", value: "'.$targetcontroller.'"});
										data.push({name: "targetation", value: "'.$targetation.'"});
										data.push({name: "model", value: "'.$ControllerArray['Model'].'"});
										data.push({name: "field", value: "'.$ControllerArray['Field'].'"});

		';
										if(isset($ControllerArray['Conditions']) && is_array($ControllerArray['Conditions'])){
											foreach($ControllerArray['Conditions'] as $_key => $_conditions){
												$output .= 'data.push({name: "data[Conditions]['.$_key.']", value: "'.$_conditions.'"});';
											}
										}

		$output .= '

										$.ajax({
											url: "'.$this->Html->url(array('controller' => $targetcontroller,'action' => $action)).'",
											dataType: "json",
											data: data,

									success: function(data) {
										response(data);
										},
									});
									},
									select: function(event,ui) {

										$("#'.$QuicksearchSearchingAutocomplet.'").val(ui.item.value);

										var data = $("#'.$QuicksearchForm.'").serializeArray();
										data.push({name: "ajax_true", value: 1});
										data.push({name: "'.$target_id.'", value: ui.item.key});

										$.ajax({
											type	: "POST",
											cache	: false,
											url		: "' . $url . '",
											data	: data,
											success: function(data) {
		    								$("#dialog").html(data);
		    								$("#dialog").show();
											}
										});
										return false;

									}
								});
								i++;
							});

							$("#'.$QuicksearchSearchingAutocomplet.'").change(function() {
								if($("#'.$QuicksearchSearchingAutocomplet.'").val() == ""){
									$("#container").load($("#'.$QuicksearchForm.'").attr("action"), {"ajax_true": 1});
								}
							});
						});

					</script>';

		return $output;
	}

	public function quickReportSearching($action,$minLength,$discription) {

		$targetcontroller = 'reportnumbers';
		$targetation = 'view';
		$url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>$targetation));

		$output = null;
		$output .= $this->Form->create('QuickReportsearch',array('id' => 'QuickReportsearchForm','class' => 'quip_search_form'));
		$output .= $this->Form->input('hidden', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => 1,
						)
					);

		$output .= $this->Form->input('searching_autocomplet', array(
						'class' =>'autocompletion searching_autocomplet',
						'label' => false,
						'div' => false,
						'title' => $discription,
						'placeholder' => $discription,
						'formaction' => 'autocomplete'
						)
					);

		$output .= $this->Form->end();

		$output .= '
					<script>
						$(function() {

							$("form#QuickReportsearchForm").on("keyup keypress", function(e) {
								var keyCode = e.keyCode || e.which;
								if(keyCode === 13) {
									e.preventDefault();
									return false;
								}
							});

							var placehoder_id = "#QuickReportsearchSearchingAutocomplet";
							if(!("placeholder" in document.createElement("input"))) {
								if($(placehoder_id).val() == "") {
									$(placehoder_id).css("color","#999");
									$(placehoder_id).val($(placehoder_id).attr("placeholder"));
								}
								$(placehoder_id).click(function() {
									if($(placehoder_id).val() == $(placehoder_id).attr("placeholder")) {
										$(placehoder_id).css("color","#000");
										$(placehoder_id).val("");
									}
								});
								$(placehoder_id).blur(function() {
									if($(placehoder_id).val() == "") {
										$(placehoder_id).css("color","#999");
										$(placehoder_id).val($(placehoder_id).attr("placeholder"));
									}
								});
							};

							var i = 0;
							$("#QuickReportsearchForm input.autocompletion").each(function(key,value){

								$(this).autocomplete({
									minLength: '.$minLength.',
									delay: 4,
									source: function(request,response) {
										$.ajax({
											url: "'.$this->Html->url(array_merge(array('controller' => 'topprojects','action' => $action),$this->request->projectvars['VarsArray'])).'",											dataType: "json",
											data: {
												term : request.term,
												targetcontroller : "'.$targetcontroller.'",
												targetation : "'.$targetation.'",
											},

									success: function(data) {
										response(data);
										},
									});
									},
									select: function(event,ui) {

										$("#QuickReportsearchSearchingAutocomplet").val(ui.item.value);

										var data = $("#QuickReportsearchForm").serializeArray();
										data.push({name: "ajax_true", value: 1});

										$.ajax({
											type	: "POST",
											cache	: false,
											url		: "' . $url . '" + ui.item.key,
											data	: data,
											success: function(data) {
		    								$("#container").html(data);
		    								$("#container").show();
											}
										});
										return false;

									}
								});
								i++;
							});
						});
					</script>';

		return $output;
	}

	public function quickOrderSearching($action,$minLength,$discription) {

		$targetcontroller = 'reportnumbers';
		$targetation = 'index';
		$url = $this->Html->url(array('controller'=>$targetcontroller,'action'=>$targetation));

		$output = null;
		$output .= $this->Form->create('QuickOrdersearch',array('id' => 'QuickOrdersearchForm','class' => 'quip_search_form'));
		$output .= $this->Form->input('hidden', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => 1,
						)
					);

		$output .= $this->Form->input('searching_autocomplet', array(
						'class' =>'autocompletion searching_autocomplet',
						'label' => false,
						'div' => false,
						'title' => $discription,
						'placeholder' => $discription,
						'formaction' => 'autocomplete'
						)
					);

		$output .= $this->Form->end();

		$output .= '
					<script>
						$(function() {

							$("form#QuickOrdersearchForm").on("keyup keypress", function(e) {
								var keyCode = e.keyCode || e.which;
								if(keyCode === 13) {
									e.preventDefault();
									return false;
								}
							});

							var placehoder_id = "#QuickOrdersearchSearchingAutocomplet";
							if(!("placeholder" in document.createElement("input"))) {
								if($(placehoder_id).val() == "") {
									$(placehoder_id).css("color","#999");
									$(placehoder_id).val($(placehoder_id).attr("placeholder"));
								}
								$(placehoder_id).click(function() {
									if($(placehoder_id).val() == $(placehoder_id).attr("placeholder")) {
										$(placehoder_id).css("color","#000");
										$(placehoder_id).val("");
									}
								});
								$(placehoder_id).blur(function() {
									if($(placehoder_id).val() == "") {
										$(placehoder_id).css("color","#999");
										$(placehoder_id).val($(placehoder_id).attr("placeholder"));
									}
								});
							};

							var i = 0;
							$("#QuickOrdersearchForm input.autocompletion").each(function(key,value){

								$(this).autocomplete({
									minLength: '.$minLength.',
									delay: 4,
									source: function(request,response) {
										$.ajax({
											url: "'.$this->Html->url(array_merge(array('controller' => 'topprojects','action' => $action),$this->request->projectvars['VarsArray'])).'",
											dataType: "json",
											data: {
												term : request.term,
												targetcontroller : "'.$targetcontroller.'",
												targetation : "'.$targetation.'",
											},

									success: function(data) {
										response(data);
										},
									});
									},
									select: function(event,ui) {

										$("#QuickOrdersearchSearchingAutocomplet").val(ui.item.value);

										var data = $("#QuickOrdersearchForm").serializeArray();
										data.push({name: "ajax_true", value: 1});

										$.ajax({
											type	: "POST",
											cache	: false,
											url		: ui.item.key,
											data	: data,
											success: function(data) {
		    								$("#container").html(data);
		    								$("#container").show();
											}
										});
										return false;

									}
								});
								i++;
							});
						});
					</script>';

		return $output;
	}

	public function showNavi($menues) {
		$output		= null;
		$output_1	= null;
		$output_2	= null;
		$output_3	= null;
		$x = 0;

		foreach ($menues as $menue){
			$output .= '<li>'.$menue['haedline'].'<ul>';
			if(is_array($menue['discription'])){

				foreach($menue['actions'] as $actions) {$_actions[] = $actions;}
				foreach($menue['params'] as $params) {$_params[] = $params;}
				foreach($menue['discription'] as $discription) {$_discription[] = $discription;}
				foreach($menue['class'] as $class) {$class[] = $class;}

				for($x = 0; $x < count($_discription); $x++) {
					$output .= '<li>'.$this->Html->link($_discription[$x], array('controller' => $menue['controller'], 'action' => $_actions[$x].'/'.$_params[$x]),array('class' => $class[$x])).'</li>';
				}
			}
			else{
				$output .= '<li>'.$this->Html->link($menue['discription'], array(
					'controller' => $menue['controller'],
					'action' => $menue['actions'].'/'.$menue['params']),
					array('class' => $menue['class'])).'</li>';
			}
			$output .= '</ul></li>';
		$x++;
		}
		return $output;
	}

	public function makeTerms($Array) {

		$terms = null;
		foreach($Array as $_Array){
			$terms .= '/';
			$terms .= $_Array;
		}
		return $terms;
	}

	public function makeLink($controller,$action,$discription,$classes,$ids,$Terms, $rel=null) {

		$terms = null;
		$output = null;

		if(!is_array($discription)){
			$desc = $discription;
			$title = $discription;
		}
		elseif(is_array($discription)){
			$desc = $discription[0];
			unset($discription[0]);
			$title = __('This report contains',true) . ' ';
			$title .= implode(PHP_EOL,$discription);
		}


		foreach($Terms as $_Terms){
			$terms .= '/';
			$terms .= $_Terms;
		}

		$output .= $this->Html->link($desc,
				array(
						'controller' => $controller,
						'action' => $action.$terms
				),
				array_filter(array(
						'class' => $classes,
						'title'=> $title,
						'id'=> $ids,
						'rel' => $rel
				))
				);

		return $output;
	}

	public function sysLinks($SettingsArray) {

		$output = null;

		if(!isset($SettingsArray)){
			return;
		}

		if(!isset($SettingsArray['refresh'])) $SettingsArray['refresh'] = array();

		$refreshArray = array(
							'discription' => __('Refresh this page'),
							'controller' => $this->request->params['controller'],
							'action' => $this->request->params['action'],
							'terms' => $this->request->projectvars['VarsArray']
						);


		$SettingsArray['refresh'] = $refreshArray;

		$output .= '<div class="settingslink">';

		$helparray = array('addsearching','addsearchingmonitoring','statistik','advancelink','expediting','progresstool','examinerlink','welderlink','devicelink','devicetestingmethodslink','documentlink','testinginstructionlink','dropdownsmasterlink','last_ten','history','backlink','addlink','editlink','assignedlink', 'assignlink','movelink','upload','settingslink', 'workloadlink','refresh');

		if(Configure::check('ExpeditingManager') && Configure::read('ExpeditingManager') == true && $this->request->projectvars['VarsArray'][0] > 0) {
			$SettingsArray['expediting'] = array();
			$SettingsArray['expediting'] = array('discription'=>__('Expediting',true),'controller'=>'suppliers','action'=>'index','terms'=>$this->request->projectvars['VarsArray']);
		}

		if(Configure::check('StatisticsEnabled') && !Configure::read('StatisticsEnabled')) {
			$_tmp = array_flip($helparray);
			unset($_tmp['statistik']);
			$helparray=array_flip($_tmp);

			unset($_tmp);
		}

		foreach($helparray as $_helparray){

			if(isset($SettingsArray[$_helparray])){

				$layout_class = 'modal';
				if($_helparray == 'refresh')$layout_class = 'ajax';
				if($_helparray == 'examinerlink')$layout_class = 'ajax';
				if($_helparray == 'welderlink')$layout_class = 'ajax';
				if($_helparray == 'devicelink')$layout_class = 'ajax';
				if($_helparray == 'documentlink')$layout_class = 'ajax';
				if($_helparray == 'expediting')$layout_class = 'ajax';
				if($_helparray == 'addsearching')$layout_class = 'ajax';
				if($_helparray == 'addsearchingmonitoring')$layout_class = 'ajax';
				if($_helparray == 'statistik')$layout_class = 'ajax';
				if($_helparray == 'testinginstructionlink')$layout_class = 'ajax';
				if($_helparray == 'dropdownsmasterlink')$layout_class = 'ajax';
				if($_helparray == 'advancelink')$layout_class = 'ajax';

				$terms = null;

				if($SettingsArray[$_helparray]['terms'] != null && is_array($SettingsArray[$_helparray]['terms'])  && count($SettingsArray[$_helparray]['terms']) > 0){
					$x = 0;
					foreach($SettingsArray[$_helparray]['terms'] as $_terms){
						$terms .= '/';
						$terms .= $_terms;
						$x++;
					}
				}

				$options = array();

				if(isset($SettingsArray[$_helparray]['disabled']) && $SettingsArray[$_helparray]['disabled']) $options['disabled']='disabled';
				if(isset($SettingsArray[$_helparray]['rel']) && $SettingsArray[$_helparray]['rel']) $options['rel']= $SettingsArray[$_helparray]['rel'];

				$output .= $this->Html->link(__('Settings'),
							array(
								'controller' => $SettingsArray[$_helparray]['controller'],
								'action' => $SettingsArray[$_helparray]['action'].$terms

								),
							array_merge(array('class' => $_helparray.' '.$layout_class, 'title'=>$SettingsArray[$_helparray]['discription']), $options));
			}
		}

		$output .= '</div>';

		return $output;
	}

	public function sysLinksModal($SettingsArray) {

		$output = null;

		$refreshArray = array(
							'discription' => __('Refresh this window'),
							'controller' => $this->request->params['controller'],
							'action' => $this->request->params['action'],
							'terms' => $this->request->projectvars['VarsArray']
						);

//		$SettingsArray['refresh'] = $refreshArray;

		$output .= '<div class="settingslink">';

		$output .= '<script type="text/javascript">
				$(document).ready(function(){
						var PositionDialog = null;
						var PositionSettingslink = null;
						var PositionDialog = $("#dialog").offset();
						var PositionSettingslink = $("#dialog .settingslink").offset();
//						$(".ui-dialog").css("top","0");
					});
				</script>';

		if(!isset($SettingsArray)){

			$output .= $this->Html->link(__('Minimize Window'),array('action' => ''),array('class' => 'closemodal mymodal','id' => 'closethismodal', 'title' => __('Close Window')));
			$output .= $this->Html->link(__('Close Window'),array('action' => ''),array('class' => 'closemodal mymodal','id' => 'closethismodal', 'title' => __('Close Window')));
			$output .= '</div>';

			return $output;
		}

		$helparray = array('showindex','examinerlink','welderlink','devicelink','userlink','certificatelink','eyechecklink','toplink','backlink','movelink','copylink','assignlink','addrepairlink','dropdownslink','dependencieslink','imagelink','filelink','beforelink','nextlink','viewlink','editlink','linklink','dellink','addlink','upload','download','addsearching','settingslink','workloadlink','printlink','historylink','refresh');

		foreach($helparray as $_helparray){

			if(!isset($SettingsArray[$_helparray])) continue;

				$terms = null;

				if($SettingsArray[$_helparray]['terms'] != null && is_array($SettingsArray[$_helparray]['terms'])  && count($SettingsArray[$_helparray]['terms']) > 0){
					$x = 0;
					foreach($SettingsArray[$_helparray]['terms'] as $_terms){
						$terms .= '/';
						$terms .= $_terms;
						$x++;
					}
				}

				$options = array();
				if(isset($SettingsArray[$_helparray]['disabled']) && $SettingsArray[$_helparray]['disabled']) $options['disabled']='disabled';
				if(isset($SettingsArray[$_helparray]['class'])) $options['class'] = $SettingsArray[$_helparray]['class'];
				if(isset($SettingsArray[$_helparray]['target'])) $options['target'] = $SettingsArray[$_helparray]['target'];
				if(isset($SettingsArray[$_helparray]['rel']) && $SettingsArray[$_helparray]['rel']) $options['rel']=$SettingsArray[$_helparray]['rel'];

				// Dataset-Optionen "data-..." noch einfügen, diese werden vom Jquery-Helper automatisch mit übernommen
/*
				$dataset = preg_grep_key('/data-/', $SettingsArray[$_helparray]);

				if(!empty($dataset)) {
					$options += $dataset;
				}
*/
				switch($_helparray) {
					case 'printlink':
					case 'downloadlink':
						$_class = $_helparray;
						break;

					default:
						$_class = $_helparray.' mymodal';
						break;
				}

				$output .= $this->Html->link(__('Settings'),
							array_merge(
								array(
									'controller' => $SettingsArray[$_helparray]['controller'],
									'action' => $SettingsArray[$_helparray]['action'].$terms
								),
								array_intersect_key($SettingsArray[$_helparray], array_flip(array_filter(array_keys($SettingsArray[$_helparray]), 'is_numeric')))
							),
//							array_merge(array('class' => $_helparray.' mymodal', 'title'=>$SettingsArray[$_helparray]['discription']), $options));
					array_merge(array('class' => $_class, 'title'=>$SettingsArray[$_helparray]['discription']), $options)
				);
		}

		$output .= $this->Html->link(__('Minimize Window'),'javascript:',array('class' => 'minimizemodal','id' => 'minimizethismodal', 'title' => __('Minimize Window')));
		$output .= $this->Html->link(__('Close Window'),'javascript:',array('class' => 'closemodal mymodal','id' => 'closethismodal','title' => __('Close Window')));

		$output .= '</div>';

		return $output;
	}

	public function showNavigation($menues) {
		$output		= null;
		$output_1	= null;
		$output_2	= null;
		$output_3	= null;
		$x = 0;

		foreach ($menues as $menue){
			$output .= '<li>'.$menue['haedline'].'<ul>';
			if(is_array($menue['discription'])){

				foreach($menue['actions'] as $actions) {$_actions[] = $actions;}
				foreach($menue['params'] as $params) {$_params[] = $params;}
				foreach($menue['discription'] as $discription) {$_discription[] = $discription;}

				for($x = 0; $x < count($_discription); $x++) {
					$output .= '<li>'.$this->Html->link($_discription[$x], array('controller' => $menue['controller'], 'action' => $_actions[$x].'/'.$_params[$x]),array('class'=>'ajax')).'</li>';
				}
			}
			else{
				$output .= '<li>'.$this->Html->link($menue['discription'], array(
					'controller' => $menue['controller'],
					'action' => $menue['actions'].'/'.$menue['params']),
					array('class'=>'ajax')).'</li>';
			}
			$output .= '</ul></li>';
		$x++;
		}
		return $output;
	}

	public function showNavigationModal($menues) {
		$output		= null;
		$output_1	= null;
		$output_2	= null;
		$output_3	= null;
		$x = 0;

		foreach ($menues as $menue){
			$output .= '<li>'.$menue['haedline'].'<ul>';
			if(is_array($menue['discription'])){

				foreach($menue['actions'] as $actions) {$_actions[] = $actions;}
				foreach($menue['params'] as $params) {$_params[] = $params;}
				foreach($menue['discription'] as $discription) {$_discription[] = $discription;}

				for($x = 0; $x < count($_discription); $x++) {
					$output .= '<li>'.$this->Html->link($_discription[$x], array('controller' => $menue['controller'], 'action' => $_actions[$x].'/'.$_params[$x]),array('class'=>'ajax')).'</li>';
				}
			}
			else{
				$output .= '<li>'.$this->Html->link($menue['discription'], array(
					'controller' => $menue['controller'],
					'action' => $menue['actions'].'/'.$menue['params']),
					array('class'=>'mymodal')).'</li>';
			}
			$output .= '</ul></li>';
		$x++;
		}
		return $output;
	}

	public function showReports($menues) {
	$output = null;
	$CssClass = 'ajax';
	foreach ($menues as $key => $menue){

		$developmentcontainer = null;

		if(isset($menue['developmentstatus'])){

			$title = null;
			$bg_color = null;
			$bg_color_inner = null;
			$title.= $menue['developmentstatus']['complett'];
			$title.= ' ' . __('from',true) . ' ';
			$title.= $menue['developmentstatus']['all'] . ' ' . __('complete',true) . ' ';

			if($menue['developmentstatus']['error'] > 0){
				 $title.= ', ' . $menue['developmentstatus']['error'] . ' ' . __('mistake(s)',true);
				 $bg_color = 'background-color:#e6a533;';
				 $bg_color_inner = 'background-color:#c93b20;';
			}

			$developmentcontainer .= '<div title="' . $title . '" class="development_info" style="' . $bg_color . 'width:' . $menue['developmentstatus']['container_width'] . 'em">';
			$developmentcontainer .= '<div title="' . $title . '" class="development_info_inner" style="' . $bg_color_inner . 'width:' . $menue['developmentstatus']['container_width_inner'] . 'em">';
			$developmentcontainer .= '</div>';
			$developmentcontainer .= '</div>';
			$title = null;
			$bg_color = null;
			$bg_color_inner = null;
		}

		if(isset($menue['haedline']) && $menue['haedline'] != ''){
			$output .= '<h4 class="listemax">'.$menue['haedline'].'</h4>';
		}
		$output .= '<ul class="listemax">';

		foreach($menue['controller'] as $_key => $_controller) {

			$output .= '<li class="icon_discription ';

			if($_controller == 'reportnumbers'){$output .= 'icon_view';}
			if($_controller == 'testingmethods'){$output .= 'icon_add';}

			if(isset($menue['class'][$_key])){$output .= ' '.$menue['class'][$_key];}

			$output .= '"><span></span>';

			if(is_array($menue['discription'])){$discription = $menue['discription'][$_key];}
			else {$discription = $menue['discription'];}

			// Wenn eine Statistik da ist
			if(isset($menue['statistik'][$_key])){
				$discription .= ' ['.$menue['statistik'][$_key]['all'].' gesamt] ';
				$discription .= ' ['.$menue['statistik'][$_key]['closed'].' geschlossen] ';
				$discription .= ' ['.$menue['statistik'][$_key]['settled'].' abgerechnet] ';
			}

			if(is_array($menue['controller'])){$controller = $menue['controller'][$_key];}
			else {$controller = $menue['controller'];}

			if(is_array($menue['action'])){$actions = $menue['action'][$_key];}
			else {$actions = $menue['action'];}

			$params = null;

			if($controller == 'testingmethods'){
				$CssClass = 'modal';
			}

			if(isset($menue['link_class']) && $menue['link_class'][$_key] != ''){
				$CssClass = $menue['link_class'][$_key];
			}

			$output .= $this->Html->link($discription, array('controller' => $controller, 'action' => $actions, $params),array('class' => $CssClass));

			$output .= $developmentcontainer;

			$output .= '</li>';

			$CssClass = 'ajax';
			$discription = null;
			$controller = null;
			$actions = null;
			$params = null;

		}

		$output .= '</ul>';

		if(isset($developmentcontainer))unset($developmentcontainer);

	}

	return $output;
	}

	public function showBreads($bread) {

		$output  = null;
		$output .= '<div class="breadcrumbs">';
		$breads = Configure::read('breadcrumpList');
		$RestrictReopenFromStatus = Configure::read('RestrictReopenFromStatus');

		$this->Html->addCrumb(__('Overview',true),FULL_BASE_URL);

		if(!is_array($bread)){

			$output .= $this->Html->getCrumbs('<span class="pfeil"> > </span>');
			$output .= '</div>';
			return $output;

		}
		if(count($bread) == 0){

			$output .= $this->Html->getCrumbs('<span class="pfeil"> > </span>');
			$output .= '</div>';
			return $output;

		}

		foreach($bread as $id => $_bread) {

			$pass = null;
			if(is_array($_bread['pass'])) {
				if(count($_bread['pass']) > 0) {
					foreach($_bread['pass'] as $_pass) {
						$pass .= '/'.$_pass;
					}
				$pass = rawurldecode($pass);
				}
				else {
					$pass = null;
				}
			}
			else {
				$pass = $_bread['pass'];
			}
			$this->Html->addCrumb($_bread['discription'],
				array(
					'controller'	=> $_bread['controller'],
					'action' 		=> $_bread['action'].'/'.$pass,
				),
				array('escape' => false)
			);
		}

		$output .= $this->Html->getCrumbs('<span class="pfeil"> > </span>');
		$output .= '</div>';

		return $output;
	}

	// zum löschen verurteilt
	public function showBreadcrumb($breadcrumbs) {

		$output  = null;
		$output .= '<div class="breadcrumbs">';

		if(isset($breadcrumbs)) {
			foreach($breadcrumbs as $_breadcrumbs) {
				$this->Html->addCrumb($_breadcrumbs['description'],
					array(
						'controller'	=> $_breadcrumbs['controller'],
						'action' 		=> $_breadcrumbs['action'].'/'.$_breadcrumbs['actionen']
					),
					array('escape' => false)
				);
			}
		}

		$output .= $this->Html->getCrumbs('<span class="pfeil"> > </span>');
		$output .= '</div>';

		return $output;
	}

	public function showReportVerificationByTime($data) {

		$output  = null;

		if(!isset($data['action'])) return $output;

		if(isset($data['Reportnumber']['revision_write']) && $data['Reportnumber']['revision_write'] == 1) return $output;

		$level = $data['Level'];
		$reopen = $data['Reopen'];

		if(intval($data['Reportnumber']['status']) < 2){
			return;
		}

		$output  .= '<span class="changestatusoutput">';

		$output  .= $this->Html->link(__('Close this report', true),
			array_merge(array('controller' => 'reportnumbers','action' => $data['action']),$this->request->projectvars['VarsArray']),
			array('id' => 'changestatus', 'rel' => $data['Reportnumber']['id'], 'class'=> 'icon ' . $data['class'], 'title'=> $data['title']));
		$output  .= '</span>';
		if($data['Reportnumber']['status'] == 0){

			$text_close_report = __('Warning, this function may not be reversed!', true);

			$output  .= '
 						<script>
							$(function() {
								$("a#changestatus").click(function() {

									checkDuplicate = confirm("'.$text_close_report.'");
									if (checkDuplicate == false) {
									return false;
								}

								var dialogsmallOpts = {
									modal: false,
									width: 450,
									height: 250,
									autoOpen: false,
									draggable: true,
									resizable: true
									};

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

									$("#dialog").dialog(dialogsmallOpts);

									var data = $("#fakeform").serializeArray();
									data.push({name: "ajax_true", value: 1});
									data.push({name: "dialog", value: 1});
									data.push({name: "controller", value: "'.$this->request->params['controller'].'"});
									data.push({name: "action", value: "'.$this->request->params['action'].'"});

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

								});
							});
						</script>
						';
		}

		if($data['Reportnumber']['status'] > 0){

			$output  .= '
 						<script>
							$(function() {
								$("form.editreport input, form.editreport select, form.editreport textarea, form.editreport button").attr("disabled", "disabled");

								var dialogsmallOpts = {
									modal: false,
									width: 450,
									height: 250,
									autoOpen: false,
									draggable: true,
									resizable: true
									};

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

								$("a#changestatus").click(function() {

									$("#dialog").dialog(dialogOpts);

									var data = $("#fakeform").serializeArray();
									data.push({name: "ajax_true", value: 1});
									data.push({name: "dialog", value: 1});
									data.push({name: "controller", value: "'.$this->request->params['controller'].'"});
									data.push({name: "action", value: "'.$this->request->params['action'].'"});

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
								});

							});
						</script>
						';

		}

		if($data['Reportnumber']['status'] == 2){
			$output  .= '
 						<script>
							$(function() {
								$("form.editreport input, form.editreport select, form.editreport textarea, form.editreport button").attr("disabled", "disabled");
							});
						</script>
						';
		}

		return $output;

	}

/* Florian - 11.09.2017    Suchheader für entsprechende Funktionen anzeigen */
	public function showSearchHeader() {
		$c = $this->request->controller;
		$a = $this->request->action;
		$_action = strtolower($c).'.'.strtolower($a);

		$type = isset($this->request->data['type']) ? $this->request->data['type'] : null;
		$links = array();

		if(array_search($_action, array('reportnumbers.search', 'orders.search', 'topprojects.search')) !== false) {
			if(Configure::read('search.orders'))
				$links[] = $this->makeLink('topprojects','search',__('Search for orders'),'mymodal'.($c=='topprojects' && $a=='search' ? ' active' : null),null,$this->request->projectvars['VarsArray']);

			if(Configure::read('search.companyRelated'))
				$links[] = $this->makeLink('testingcomps','search', __('Search company related'), 'mymodal'.($c=='testingcomps' && $a=='search' && empty($type) ? ' active' : null), null, $this->request->projectvars['VarsArray']);

			$links[] = $this->makeLink('reportnumbers','search',__('Search for reports'),'mymodal'.($c=='reportnumbers' && $a=='search' ? ' active' : null),null,$this->request->projectvars['VarsArray']);
		}

		if(Configure::read('search.examiner') && array_search($_action, array('examiners.search', 'testingcomps.search')) !== false && $type == 'examiners')
			$links[] = $this->makeLink('testingcomps','search', __('Search examiners'), 'mymodal'.($c=='testingcomps' && $a=='search' && $type == 'examiners' ? ' active' : null), null, $this->request->projectvars['VarsArray'], 'examiners');

		if(Configure::read('search.devices') && array_search($_action, array('devices.search', 'testingcomps.search')) !== false && $type == 'devices')
			$links[] = $this->makeLink('testingcomps','search', __('Search devices'), 'mymodal'.($c=='testingcomps' && $a=='search' && $type == 'devices' ? ' active' : null), null, $this->request->projectvars['VarsArray'], 'devices');

		$output = null;
		if(count($links) > 1) {
			$output = join(' ', $links);
			$output .= $this->Html->tag('span', '', array('class'=>'clear'));

			$output = $this->Html->tag('legend', $output, array('class'=>'links'));
		}

		return $output;
	}
/* Florian - 11.09.2017 */

	public function showReportVerification($data) {
		$level = Configure::check('RestrictReopenFromStatus') ? intval(Configure::read('RestrictReopenFromStatus')) : 2;
		$reopen = !Configure::check('AllowReopen') || $data['Reportnumber']['status'] < $level || (bool)Configure::read('AllowReopen');
		$output  = null;

		switch(intval($data['Reportnumber']['status'])) {
			case 1:

				if(Configure::check('RevisionInReport') && Configure::read('RevisionInReport') == true) return $output;

				$thisAction = $reopen ? 'status2' : 'versionize';
				$thisClass = 'closed1'.($reopen ? null : ' versionize');
				$thisTitle = $reopen ? __('Close this report', true) : __('Create new version');
				break;

			case 2:

				if(Configure::check('RevisionInReport') && Configure::read('RevisionInReport') == true) return $output;

				$thisAction = $reopen ? 'status2' : 'versionize';
				$thisClass = 'closed2'.($reopen ? null : ' versionize');
				$thisTitle = $reopen ? __('Reopen this Report', true) : __('Create new version');
				break;

			case 3:

				if(Configure::check('RevisionInReport') && Configure::read('RevisionInReport') == true) return $output;

				$thisAction = $reopen ? 'status2' : 'versionize';
				$thisClass = 'closed3'.($reopen ? null : ' versionize');
				$thisTitle = $reopen ? __('Reopen this Report', true) : __('Create new version');
				break;

			default:
				$thisAction = 'status1';
				$thisClass = 'close';
				$thisTitle = __('Close this report', true);
				break;
		}

		$output  .= '<span class="changestatusoutput">';

		$output  .= $this->Html->link(__('Close this report', true),
			array_merge(
			array(
				'controller'=>'reportnumbers',
				'action' => $thisAction,
			),
			$this->request->projectvars['VarsArray']
			),
			array('id' => 'changestatus', 'rel' => $data['Reportnumber']['id'], 'class'=> 'icon ' . $thisClass, 'title'=> $thisTitle));

		$output  .= '</span>';

		if($data['Reportnumber']['status'] == 0){

			$text_close_report = __('Warning, this function may not be reversed!', true);

			$output  .= '
 						<script>
							$(function() {

								$("a#changestatus").click(function() {
									checkDuplicate = confirm("'.$text_close_report.'");
									if (checkDuplicate == false) {
									return false;
								}

								var dialogsmallOpts = {
									modal: false,
									width: 450,
									height: 250,
									autoOpen: false,
									draggable: true,
									resizable: true,
									close: function(event,ui){
										$("#dialog").empty();
										}
									};

									$("#dialog").dialog(dialogsmallOpts);

									var data = $("#fakeform").serializeArray();
									data.push({name: "ajax_true", value: 1});
									data.push({name: "dialog", value: 1});
									data.push({name: "controller", value: "'.$this->request->params['controller'].'"});
									data.push({name: "action", value: "'.$this->request->params['action'].'"});

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
								});
							});
						</script>
						';
		}

		if($data['Reportnumber']['status'] > 0){

			$output  .= '
 						<script>
							$(function() {

								var dialogsmallOpts = {
									modal: false,
									width: 450,
									height: 250,
									autoOpen: false,
									draggable: true,
									resizable: true
									};

								$("a#changestatus").click(function() {
									if($(this).hasClass("versionize")) {
										if(false == confirm("'.__('The newly created report will get a new reportnumber. Are you sure you want to continue?').'")) {
											return false;
										}
									}

									$("#dialog").dialog(dialogsmallOpts);

									var data = $("#fakeform").serializeArray();
									data.push({name: "ajax_true", value: 1});
									data.push({name: "dialog", value: 1});
									data.push({name: "controller", value: "'.$this->request->params['controller'].'"});
									data.push({name: "action", value: "'.$this->request->params['action'].'"});

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
								});

							});
						</script>
						';

		}

		return $output;

	}

// Löschen
	public function showReportMenue($ReportMenue,$data,$settings) {

		$output  = null;

		foreach($ReportMenue['Menue'] as $_key => $_ReportMenue){

			$CssClass = 'icon ';
			$CssClass .= isset($_ReportMenue['class']) ? $_ReportMenue['class'] : null;

		$output  .= $this->Html->link($_ReportMenue['title'],
			array_merge(
				array(
					'controller' => $_ReportMenue['controller'],
					'action' => $_ReportMenue['action']
					),
				$_ReportMenue['vars']
				),
				array(
					'class' => $CssClass,
					'title' => isset($_ReportMenue['title']) ? $_ReportMenue['title'] : null,
					'id' => isset($_ReportMenue['id']) ? $_ReportMenue['id'] : null,
					'target' => isset($_ReportMenue['target']) ? $_ReportMenue['target'] : null,
					'disabled' => isset($_ReportMenue['disabled']) ? $_ReportMenue['disabled'] : null,
				)
			);

		}

		// Wenn der Report gelöscht wurden
		if($data['Reportnumber']['delete'] > 0){
			$output .= '<div class="clear"></div>';
			return $output;
		}

		$output  .= '
 					<script>
						$(function() {
							$(".edit a").tooltip({
								track: true
								});

						});
					</script>
					';
		if(isset($ReportMenue['CloseMethode']['Methode'])){

			$closeMethod = $ReportMenue['CloseMethode']['Methode'];
			if(!method_exists($this, $closeMethod) || !preg_match('/^showreportverification/', strtolower($closeMethod))) {
				$closeMethod = 'showReportVerification';
			}

			$output .= call_user_func(array($this, $closeMethod), $ReportMenue['CloseMethode']);
//			$output .= call_user_func(array($this, $closeMethod), $this->_View->viewVars['reportnumber']);
		}

		$text_delete_report = __('Are you sure you want to delete this Report?', true);

		$output  .= '
 					<script>
						$(function() {
							$("#text_delete_report").click(function() {
								checkDuplicate = confirm("'.$text_delete_report.'");

								if (checkDuplicate == false) {
									return false;
								}
							});
						});
					</script>
					';

		$output .= '<div class="clear"></div>';
		return $output;
	}

	public function showWaitingMenue($projectID,$orderID) {
		$output  = null;

		$output  .= $this->Html->link(__('Print this report', true),
			array('action' => 'pdf', $projectID, $orderID),
			array('class'=>'print','target'=>'' ,'title'=> __('Print this waiting report', true)));

		return $output;
	}

	public function EditMenue($editmenue) {

		if(empty($this->request->data['EditMenue']['Menue'])){return;}

		$editmenue = $this->request->data['EditMenue']['Menue'];

		$output  = null;
		$output .= '<ul class="editmenue">';
		foreach($editmenue as $_editmenue){
			$_editmenue = array_merge(array(
				'class'=>null,
				'id'=>null,
				'discription'=>null,
				'controller'=>$this->request->params['controller'],
				'action'=>$this->request->params['action'],
				'parms'=>$this->_View->viewVars['VarsArray']
			), $_editmenue);

			$output .= '<li class="'.$_editmenue['class'].'" id="Li_'.$_editmenue['id'].'">';

			$output .=  $this->Html->link($_editmenue['discription'],
				array(
					'controller' => $_editmenue['controller'],
					'action' => $_editmenue['action'],
						$_editmenue['parms'][0],
						$_editmenue['parms'][1],
						$_editmenue['parms'][2],
						$_editmenue['parms'][3],
						$_editmenue['parms'][4],
						$_editmenue['parms'][5]
					),
				array(
					'class' => $_editmenue['rel'].' '.$_editmenue['class'],
					'id' => $_editmenue['id'],
					'rel' => $_editmenue['rel']
					)
				);
			$output .= '</li>';
		}
		$output .= '</ul>';

		return $output;
	}

	public function ContextMenue($separator = null,$data) {

		$output = null;

		if($separator == null)$separator = 'hasmenu1';
		if(!is_array($data)) return;
		if(count($data) == 0) return;

$output .= '
		$("span.for_' . $separator . '").contextmenu({
			delegate: ".' . $separator . '",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [';

foreach($data as $_key => $_SubMenueArray){

$output .= '
				{
				title: "' . $_SubMenueArray['title'] . '",
				cmd: "' . $_SubMenueArray['action'] . '",
';

switch ($_SubMenueArray['open']) {
case 'window':
	$output .= '
				action :	function(event, ui) {
								window.open("' . $_SubMenueArray['controller'] . '/' . $_SubMenueArray['action'] . '/" + ui.target.attr("rev"));
							},
	';
	break;
case 'container':
	$output .= '
				action :	function(event, ui) {
							$("#container").load("' . $_SubMenueArray['controller'] . '/' . $_SubMenueArray['action'] . '/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
							},
	';
	break;
case 'dialog':
	$output .= '
				action :	function(event, ui) {

					$("#AjaxSvgLoader").show();

					var url = "' . $_SubMenueArray['controller'] . '/' . $_SubMenueArray['action'] . '/"  + ui.target.attr("rev");

				//	$(".ui-dialog").show();
					$("#dialog").dialog().dialog("close");
					$("#maximizethismodal").hide();

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

					var data = new Array();
					data.push({name: "ajax_true", value: 1});
					data.push({name: "dialog", value: 1});

					$("#dialog").empty();

					$.ajax({
						type	: "POST",
						cache	: false,
						url		: url,
						data	: data,
						success: function(data) {
							$("#dialog").html(data);
							$("#dialog").dialog("open");
							$("#dialog").show();
							$("#dialog").css("overflow","scroll");
							$("#AjaxSvgLoader").hide();
						},
						statusCode: {
					    404: function() {
					      alert( "page not found" );
								location.reload();
					    }
					  },
						statusCode: {
					    403: function() {
					      alert( "page blocked" );
								location.reload();
					    }
					  }
						});



							},
	';
	break;
default:
	$output .= '
				action :	function(event, ui) {
								$("#dialog").load("' . $_SubMenueArray['controller'] . '/' . $_SubMenueArray['action'] . '/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
								$("#dialog").dialog("open");
							},
	';
	break;
}


$output .= '
				uiIcon: "' . $_SubMenueArray['uiIcon'] . '"
				},
				{
				title: "----"
				},
';

}

$output .= '
				],
			select: function(event, ui) {},
			});
';

	return $output;

	}
}
