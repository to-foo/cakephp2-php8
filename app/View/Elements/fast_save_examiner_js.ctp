<div class="clear" id="savediv"></div>
<?php
$url = $this->Html->url(array_merge(array('action'=>'save'),$this->request->projectvars['VarsArray']));
?>
<script type="text/javascript">
$(document).ready(function(){

	$('form.dialogform').on('keyup keypress', function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	var data = $("#fakeform").serializeArray();
	var orginal_data = {};

	$("form.dialogform input, form.dialogform select, form.dialogform textarea").each(function(){
		if($(this).closest("div").hasClass("radio")){
			if($(this).attr("checked") == "checked"){
				orginal_data["orginal_" + $(this).attr("name")] = $(this).val();
			}
		}
		if($(this).closest("div").hasClass("text") || $(this).closest("div").hasClass("select") || $(this).closest("div").hasClass("textarea")) {
			orginal_data["orginal_" + $(this).attr("name")] = $(this).val();
		}
		if($(this).closest("div").hasClass("checkbox")){
			if($(this).attr("checked") === undefined){
				orginal_data["orginal_" + $(this).attr("name")] = 0;
			} else {
				orginal_data["orginal_" + $(this).attr("name")] = 1;
			}
		}
	});

	$('#dialog').off('change', '.dependency, .customValue');

	$('#dialog').on('change', '.dependency, .customValue', function(event) {
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
		var data = $("#fakeform").serializeArray();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "this_id", value: this_id});
		data.push({name: $(this).attr("name"), value: val});

		$.each(orginal_data, function(key, value) {
			data.push({name: key, value: value});
		});

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

	$("form.dialogform input, form.dialogform select, form.dialogform textarea").change(function() {
		$("#content .edit a.print").data('prevent',1);
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

		var val = $(this).val();
		var this_id = $(this).attr("id");
		var this_submit_id = $(this).attr("id") + "_submit";
		var name = $(this).attr("name");

		$("<span id=\"" + this_submit_id + "\"></span>").insertAfter("#" + this_id);

		if($(this).closest("div").hasClass("select")){
			if($(this).find("option:selected").attr("rel") == "custom"){
				$(this).closest("div").find("label").css("background-image","none");

				return false;
			}
			val = $(this).find('option:selected').text();
		}
		if($(this).closest("div").hasClass("checkbox")){
			if($(this).attr("checked") === undefined){
				val = 0;
			} else {
				val = 1;
			}
		}

		if($(this).attr("multiple") != undefined){
			$(this).parent().find("input").val($(this).val() == null ? "" : $(this).val().join(", "));
			val = $(this).parent().find("input").val();
			name = $(this).parent().find("input").attr("name");

			if(name == undefined && val == undefined ){
				val = $(this).parent().find("textarea").val();
				name = $(this).parent().find("textarea").attr("name");
			}
		}

		var url = "<?php echo $url;?>";
		var data = $("#fakeform").serializeArray();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "this_id", value: this_id});
		data.push({name: name, value: val});

		$.each(orginal_data, function(key, value) {
			data.push({name: key, value: value});
		});

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
				$("#dialog .edit a.print").data('prevent',0);
			}
		});

		$(this).closest("div").css("background-image","none");
		$(this).closest("div").find("label").css("background-image","none");
		$(this).css("background-color","#fff");
		return false;
	});
});
</script>
