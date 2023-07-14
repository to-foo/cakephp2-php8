<div class="clear" id="savediv"></div>
<?php
$url = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'save'),$this->request->projectvars['VarsArray']));
$testingmethod = $this->request->Verfahren;
?>
<script type="text/javascript">
$(document).ready(function(){
	let testingmethod = '<?php if(isset($testingmethod) && !empty($testingmethod)){echo $testingmethod;} else{echo '';} ?>';

	$('form.editreport').on('keyup keypress', function(e) {

		activeObj = document.activeElement;

		if(activeObj.tagName == "TEXTAREA"){
			// do nothing
		} else {
			var keyCode = e.keyCode || e.which;
			if(keyCode === 13) {
				e.preventDefault();
				return false;
			}
		}
	});

	var data = new Array();
	var orginal_data = {'controller':'<?php echo $this->request->controller; ?>', 'action':'<?php echo $this->request->action; ?>'};

	$("form.editreport input:not(.hide_box), form.editreport select, form.editreport textarea").each(function(){


		if($(this).closest("div").hasClass("radio")){

//			var active_div_width = $(this).closest("div").width() + 20;
//			$(this).closest("div").css("width",active_div_width+"px");

			if($(this).attr("checked") == "checked"){
				orginal_data["orginal_" + $(this).attr("name")] = $(this).val();
			} else {
				orginal_data["orginal_" + $(this).attr("name")] = 0;
			}
		}
		if($(this).closest("div").hasClass("number") || $(this).closest("div").hasClass("text") || $(this).closest("div").hasClass("select") || $(this).closest("div").hasClass("textarea")) {

			var active_div_width = $(this).closest("div").width() + 20;

			orginal_data["orginal_" + $(this).attr("name")] = $(this).val();
		}
		if($(this).closest("div").hasClass("checkbox")){

			var active_div_width = $(this).closest("div").width() + 20;

			if($(this).attr("checked") === undefined){
				orginal_data["orginal_" + $(this).attr("name")] = 0;
			} else {
				orginal_data["orginal_" + $(this).attr("name")] = 1;
			}
		}
	});

	$('#container').off('change', '.dependency, .customValue');

	$('#container').on('change', '.dependency, .customValue', function(event) {

		$(this).closest("div").find("label").css("padding-right","1.5em");
		$(this).closest("div").find("label").css("background-image","url(img/indicator.gif)");
		$(this).closest("div").find("label").css("background-repeat","no-repeat");
		$(this).closest("div").find("label").css("background-position","center right");
		$(this).closest("div").find("label").css("background-size","auto 0.9em");

		var val = $(this).val();
		var this_id = $(this).attr("id");
		var this_submit_id = $(this).attr("id") + "_submit";

		$("<span id=\"" + this_submit_id + "\"></span>").insertAfter("#savediv");

		var url = "<?php echo $url;?>";
		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "this_id", value: this_id});
		data.push({name: $(this).attr("name"), value: val});

		if(testingmethod != undefined) {
//			testingmethod = testingmethod[0].toUpperCase() + testingmethod.slice(1);
			let examiner_id = $("#Report" + testingmethod + "GenerallyExaminer").val();
			let supervisor_id = $("#Report" + testingmethod + "GenerallySupervision").val();

			if(!examiner_id !== "") {
				data.push({name: "examiner_id", value: examiner_id});
			}

			if(!supervisor_id !== ""){
				data.push({name: "supervisor_id", value: supervisor_id});
			}
		}

		if($(this).hasClass("edit_after_closing")){
			data.push({name: "edit_after_closing", value: 1});
		}

		$.each(orginal_data, function(key, value) {
			data.push({name: key, value: value});
		});

		$(this).removeClass("attention_field");

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			success: function(data) {
		    	$("#" + this_submit_id).html(data);
		    	$("#" + this_submit_id).show();
				}
			});

		$(this).closest("div").find("label").css("background-image","none");
		return false;
	});

	$("form.editreport input:not(.hide_box), form.editreport select, form.editreport textarea").change(function() {

		$("#content .edit a.print").data('prevent',1);

//		var active_div_width = $(this).closest("div").width() + 10;
//		$(this).closest("div").css("width",active_div_width+"px")

		if(!$(this).closest("div").hasClass("radio")){
			$(this).attr({"disabled":"disabled","rel":"saving"});
			$(this).closest("div").css("background-image","url(img/indicator.gif)");
			$(this).closest("div").css("background-repeat","no-repeat");
			$(this).closest("div").css("background-position","95% 10%");
			$(this).closest("div").css("background-size","auto 0.9em");
		}
		if($(this).closest("div").hasClass("radio")){
			$(this).closest("div").find("legend").css("padding-right","2em");
			$(this).closest("div").find("legend").css("background-image","url(img/indicator.gif)");
			$(this).closest("div").find("legend").css("background-repeat","no-repeat");
			$(this).closest("div").find("legend").css("background-position","center right");
			$(this).closest("div").find("legend").css("background-size","auto 0.9em");
		}

		if(!$(this).closest("div").hasClass("radio")){
			$("label.ui-button").css("background-image","none");
		}

		if($(this).hasClass("attention_field")){
			$(this).removeClass("attention_field");
		};

		var val = $(this).val();
		var this_id = $(this).attr("id");
		var this_submit_id = $(this).attr("id") + "_submit";
		var name = $(this).attr("name");

		$("<span id=\"" + this_submit_id + "\"></span>").insertAfter("#" + this_id);

		// Bei Multiselectfeldern nicht das Select als Quelle verwenden, wenn das Event vom Textfeld kommt.
		if($(this).closest("div").hasClass("select") && $(this).attr("multiple") == undefined){
			if($(this).is('select')) {
				if($(this).find("option:selected").attr("rel") == "custom"){
					$(this).closest("div").find("label").css("background-image","none");

					return false;
				}
				val = $(this).find('option:selected').text();
			} else {
				// In den Eingabefeldern verwendete Inhalte in der Multiselectbox ausw�hlen
				$(this).siblings('select').val($(this).val().split(/[\r\n ,;]+/));
			}
		}

		if($(this).closest("div").hasClass("checkbox")){
			if($(this).attr("checked") === undefined){
				val = 0;
			} else {
				val = 1;
			}
		}

		if($(this).attr("multiple") != undefined){

			if($(this).attr("id") == "ReportRtEvaluationError"){

			} else {

			if(val.length > 0){
				input = "";
				$(val).each(function(key,value){
					if(value.length > 0){
						text = $("#"+this_id+" option[value="+value+"]").text();
						if(text.length > 0){
							input += text + "\n";
						}
					}
				});
			} else {
				input = null;
			}

			val = input;
			}
		}

		var url = "<?php echo $url;?>";
		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "this_id", value: this_id});
		data.push({name: name, value: val});

		$.each(orginal_data, function(key, value) {
			data.push({name: key, value: value});
		});

		if($(this).hasClass("edit_after_closing")){
			data.push({name: "edit_after_closing", value: 1});
		}

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			success: function(data) {
		    	$("#" + this_submit_id).html(data);
		    	$("#" + this_submit_id).show();
			},
			complete: function(data) {
				$("#content .edit a.print").data('prevent',0);
			}
		});

		$(this).closest("div").css("background-image","none");
		$(this).closest("div").find("label").css("background-image","none");
		$(this).css("background-color","#fff");
		return false;
	});
});
</script>
