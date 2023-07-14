<script type="text/javascript">
$(document).ready(function() {

	function CssDropdownSearchStart() {
		$("#ImageCountImagesForm select").css("background-image","url(img/indicator.gif)");
		$("#ImageCountImagesForm select").css("background-repeat","no-repeat");
		$("#ImageCountImagesForm select, #GenerallySearchForm input").css("background-position","2px 2px");
		$("#ImageCountImagesForm select, #GenerallySearchForm input").css("background-size","auto 8px");
	}

	function CssDropdownSearchStop() {
		$("#ImageCountImagesForm select").css("background-image","none");
	}

	var datastart = new Array();
	datastart.push({name: "ajax_true", value: 1});
	CssDropdownSearchStart();

	$.ajax({
		type	: "POST",
		cache	: true,
		url		: "<?php echo Router::url(array_merge(array('action'=>'imagecount'), $this->request->projectvars['VarsArray']));?>",
		data	: datastart,
		dataType: "json",
		success: function(data) {
		$("#ImageCountImagecount option[value=" + data.count + "]").attr('selected', 'selected');
			CssDropdownSearchStop();
		},
	});

	$("#ImageCountImagecount").change(function() {

		CssDropdownSearchStart();

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "count", value: $(this).val()});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array_merge(array('action'=>'imagecount'), $this->request->projectvars['VarsArray']));?>",
			data	: data,
			dataType: "json",
			success: function(data) {
			$("#ImageCountImagecount option[value=" + data.count + "]").attr('selected', 'selected');
				CssDropdownSearchStop();
			},
		});
	});

});
</script>
