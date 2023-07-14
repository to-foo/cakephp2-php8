<?php
$Options = array();
$Options['label'] = false;
$Options['div'] = false;
$Options['options'] = $TestingcompsDropdown;

if(count($TestingcompsDropdown) > 1) $Options['empty'] = __('choose one',true);
if(isset($LastSelectedExaminerTestingcomp) && !empty($LastSelectedExaminerTestingcomp)) $Options['selected'] = $LastSelectedExaminerTestingcomp;

echo $this->Form->input('ExaminerTestingcomp',$Options);
?>

<script type="text/javascript">
$(document).ready(function(){
	$("select#ExaminerExaminerTestingcomp").change(function() {

	$("#AjaxSvgLoader").show();

	var data = $("#SortExamierTable").serializeArray();
	data.push({name: "ajax_true", value: 1});

	let url = window.location.href + "examiners/index";

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: url,
		data	: data,
		success: function(data) {
			$("#container").html(data);
			$("#container").show();
			$("#AjaxSvgLoader").hide();
			}
		});
		return false;
	});
});
</script>
