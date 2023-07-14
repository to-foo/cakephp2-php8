<script type="text/javascript">
$(document).ready(function(){

	function changeDependencies(input,step) {

		if(input.parent().find(".modal.dependency").length > 0){

			link = input.parent().find(".modal.dependency");

			if($(link).hasClass("hide_link")) $(link).hide();

			link = $(link).attr("href");
			link = link.replace("index", "get");

		} else {
			link = null;
		}

		if(link == null) return false;

		id = input.attr("index(selector/element)");
		oldvalue =  input.val();
		data = new Array();

		if(input.attr("multiple") == undefined){

			data.push({name: "field", value: "select"});
			data.push({name: input.attr("name"), value: input.val()});
			data.push({name: "val", value: oldvalue});

		} else if (input.attr("multiple") == "multiple") {

			input.parent("div.select").removeAttr("style");
			input.nextAll("a.dropdown").css("margin-top","-9em");
			input.nextAll("a.dependency").css("margin-top","-7em");

			if(step == "initial") return false;

			if(input.val.length > 0){

				insert = new Array();

				$(input.val()).each(function(key,value){
					if(value.length > 0) insert.push(value);
				});

				oldvalue = insert;
				data.push({name: input.attr("name"), value: insert});
				data.push({name: "val", value: oldvalue});

			} else {

			}
			data.push({name: "field", value: "multiple"});
		}

		data.push({name: "ajax_true", value: 1});
		data.push({name: "id", value: input.attr("id")});
		data.push({name: "type", value: "Report"});
		data.push({name: "step", value: step});

		$.ajax({
			url: link,
			type: "POST",
			data: data,
			dataType: "json",
			success: function(data) {

				if(typeof(data.Dropdown) != "undefined") {

					$.each(data.Dropdown,function(key,value) {

						var item = "#" + key;

						closestdiv = $(item).closest("div");
						oldselect = $(item);
						oldvalue = $(item).val();
						oldtext = $(item + " option:selected" ).text();
						oldclass = $(item).attr("class");
						emptyvalue = null;

						closestdiv.find("a").hide();

						$(item).find('option').remove().end();

						$.each(value.options, function(index, option){

							if(oldtext == option){
								$("<option>", {
										selected: "selected",
		            		value: index,
			            	text: option
			        		}).appendTo($(item));
							} else {
								$("<option>", {
										value: index,
										text: option
									}).appendTo($(item));
							}
			    	});

						if(typeof(value.rules) != "undefined") {
							$.each(value.rules.rule, function(index, option){
								if(option.type == "no_dublicated_entries"){
									if(option.value == 0){
										otherval = $("#" + option.model + option.field).val();
										thisval = $("#" + option.model + option.thisfield).val();
										$("#" + option.model + option.field + " option").prop("disabled", false);
										$("#" + option.model + option.thisfield + " option").prop("disabled", false);
										$("#" + option.model + option.field).find('option[value="' + thisval + '"]').prop("disabled", true);
										$("#" + option.model + option.thisfield).find('option[value="' + otherval + '"]').prop("disabled", true);
									} else {
										otherval = $("#" + option.model + option.field).val();
										$("#" + option.model + option.field + " option").prop("disabled", false);
										$("#" + option.model + option.thisfield + " option").prop("disabled", false);
										$("#" + option.model + option.field).find('option[value="' + option.value + '"]').prop("disabled", true);
										$("#" + option.model + option.thisfield).find('option[value="' + otherval + '"]').prop("disabled", true);
									}
								}
							});
						}
					});
				}

				if(typeof(data.Depentency) != "undefined") {

					$.each(data.Depentency,function(key,value) {

						var item = "#" + key;

//						$(".modul_field_error").remove();
						$(item).show();

						closestdiv = $(item).closest("div");
						oldselect = $(item);
						oldvalue = $(item).val();
						emptyvalue = null;

//						closestdiv.find("a").hide();
						$(item).remove();
						$(item + "ModulError").remove();
						$("div.add_empty_depentency").remove();
//						console.log(input);

						if(value.save != undefined && value.save == "direct" && step == "update"){

							insert = "";
							insertinfo = "";

							$.each(value.options,function(key1,value2) {
								insert += value2 + "\n";
								insertinfo += value2 + "<br>";
							});

							hiddeninput = '<textarea id="'+ key +'" disabled="disabled" class="dependency dependency_" name="'+ value.name +'">'+insert+'</textarea>';
							hiddeninput = $(hiddeninput);
							$(hiddeninput).hide();
							$("form.editreport").append(hiddeninput);
							hiddeninput.trigger('change');

							if(value.length == 0){
								return false;
							}

							dependent_link = "";
							dependent_link += '<div class="add_empty_depentency">';
							dependent_link += '<p><b><?php echo __('Added dependent values',true);?></b></p><p><b>';
							dependent_link += value.description;
							dependent_link += '</b></p><p>';
							dependent_link += insertinfo;
							dependent_link += '</p></div>';
							$("form.editreport").append(dependent_link);

							return false;
						}

						if(data.Reportnumber.status > 0) disabled = "disabled='disabled'";
						else disabled = '';

						newselect = '<select id="'+ key +'" class="dependency dependency_" name="'+ value.name +'" '+ disabled +'></select>';
						newinput = '<input id="'+ key +'" class="dependency dependency_" name="'+ value.name +'" value=""  '+ disabled +'/>';
						emptyinput = '<input id="'+ key +'" disabled="disabled" class="dependency dependency_" name="'+ value.name +' value=""/>';

						selectElem = $(newselect);
						inputElem = $(newinput);
						emptyElem = $(emptyinput);

						if(value.length == 0){
							inputElem.val("");
						}

						if(value.length == 1){
							$.each(value.options, function(index, option){
								inputval = option;
								inputElem.val(inputval);
				    	});
						}

						if(value.length > 1){
							$("<option>", {selected: "selected",value: "",text: ""}).appendTo(selectElem);
							$.each(value.options, function(index, option){
			        	$("<option>", {value: index,text: option}).appendTo(selectElem);
				    	});
						}

						if(step == "initial" && value.length == 0){
							inputElem.val(oldvalue);
							$(closestdiv).append(inputElem);
						}

						if(step == "initial" && value.length == 1){
							$(closestdiv).append(inputElem);
						}

						if(step == "initial" && value.length > 1){
							$(closestdiv).append(selectElem);
						}

						if(step == "update" && value.length == 0){
							$(inputElem).remove();
							$(closestdiv).append(inputElem);
							inputElem.val("").trigger('change');
						}

						if(step == "update" && value.length == 1){
							$(closestdiv).append(inputElem);
							inputElem.val(inputval).trigger('change');
						}

						if(step == "update" && value.length > 1){
							$(closestdiv).append(selectElem);
						}

						if(step == "initial"){
							$(item).val(oldvalue);
						}
					});
				}

				if(typeof(data.ModulSuccess) != "undefined") {
					$.each(data.ModulSuccess,function(key,value) {
						var item = "#" + key;
						closestdiv = $(item).closest("div");
						infolink = '<a href="javascript:" class="icon icon_certificate_success tooltip" title="' + value.text + '">' + value.text + '</a>';
						closestdiv.append(infolink);
					});
				}

				if(typeof(data.ModulError) != "undefined") {
					$.each(data.ModulError,function(key,value) {
						modulerrorfield = '<p id="' + key + 'ModulError" class="modul_field_error">' + value.text + "</p>";
						item = "#" + key;
						$(modulerrorfield).insertAfter(item);
						$(item).hide();
					});
				}

				$("a.tooltip").tooltip();

			}
		});
	}

	$('select.hasDependencies').each(function(){
		changeDependencies($(this),"initial");
	})

	$("select.hasDependencies").on("change", function() {
		changeDependencies($(this),"update");
	});
});
</script>
