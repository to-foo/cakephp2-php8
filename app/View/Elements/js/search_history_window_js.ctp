<script type="text/javascript">
$(function(){

	$("a.search_history").click(function() {

		var data = new Array();
		data.push({name: "history", value: $(this).attr("rev")});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: "<?php echo $url;?>",
			data	: data,
			dataType: "json",
				success: function(data) {
					ChangeDropdownOptions(data);
				}
			});
	
		return false;

	});
});
</script>
