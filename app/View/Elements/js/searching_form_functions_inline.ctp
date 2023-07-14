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
		} else {
			$("#SendThisReportForm").text("<?php echo __('Non testing reports found',true);?>");
			$("#SendThisReportForm").attr("disabled","disabled");
		}

		if(data.CountOfOrders > 0){
			$("#SendThisOrderForm").text(data.CountOfOrders + " " + "<?php echo __('orders found',true)?>");
			$("#SendThisOrderForm").removeAttr("disabled");
		} else {
			$("#SendThisOrderForm").text("<?php echo __('Non orders found',true);?>");
			$("#SendThisOrderForm").attr("disabled","disabled");
		}

		$.each(data,function(key,value) {
			
			$.each(value,function(key2,value2) {

				if(key2 == "Created" || key2 == "DateOfTest"){
					idstart = key + key2 + "Start";
					idend = key + key2 + "End";
					namestart = $("#" + key + key2 + "Start").attr("name");
					nameend = $("#" + key + key2 + "End").attr("name");
					valstart = $("#" + key + key2 + "Start").val();
					valend = $("#" + key + key2 + "End").val();

					$("#" + key + key2 + "Start").val(value2.start);
					$("#" + key + key2 + "End").val(value2.end);

/*
					$("#" + key + key2 + "Start, #" + key + key2 + "End").prop({"readOnly": false}).datetimepicker({
						timepicker:false, 
						format:"Y-m-d", 
						lang:"de",
						minDate: new Date(value2.start),
						maxDate: new Date(value2.end),	
					});
*/					

					searchdata.push({id: idstart, name: namestart, value: value2.start});
					searchdata.push({id: idend, name: nameend, value: value2.end});

				}
				
				if($("#" + key + key2).hasClass("dropdown")){

					thisval = $("#" + key + key2).val();
					thisid = key + key2;
					thisname = $("#" + key + key2).attr("name");
					
					searchdata.push({id: thisid, name: thisname, value: thisval});
										
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
			});
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
		} else {
			$("button#SendThisReportForm").show();
		}

		CssDropdownSearchStop();
		
	}
