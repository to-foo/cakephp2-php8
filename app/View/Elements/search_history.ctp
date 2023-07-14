<?php
if(!isset($SearchFormData['History'])) return;
if(count($SearchFormData['History']) == 1) return;

?>
<div class="clear"></div>
</ul><?php pr($ExpeditingSelect);?>
<div class="clear"></div>

<script type="text/javascript">
$(document).ready(function(){
	
	$("select.filter").change(function() {

			CssContainerWait("suppliere_container");

			var project_id = "<?php echo $this->request->projectvars['VarsArray'][0];?>";
			var cascade_id = "<?php echo $this->request->projectvars['VarsArray'][1];?>";
			var controller = "<?php echo $ExpeditingSelect['controller'];?>";
			var action = "<?php echo $ExpeditingSelect['action'];?>";
			var data = new Array();
			var url = controller+"/"+action+"/"+project_id+"/"+cascade_id;

			data.push({name: "ajax_true", value: 1});
			if($("#supplier").val().length > 0) data.push({name: "data[Statistic][supplier]", value: $("select#supplier :selected").text()});
			if($("#status").val().length > 0) data.push({name: "data[Statistic][status]", value: $("select#status").val()});
			if($("#ordered").val().length > 0) data.push({name: "data[Statistic][ordered]", value: $("select#ordered").val()});
			if($("#planner").val().length > 0) data.push({name: "data[Statistic][planner]", value: $("select#planner :selected").text()});
			if($("#area_of_responsibility").val().length > 0) data.push({name: "data[Statistic][area_of_responsibility]", value: $("select#area_of_responsibility :selected").text()});
 	
			$.ajax({
				type	: "POST",
				cache	: true,
				url		: url,
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