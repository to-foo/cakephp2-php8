<script type="text/javascript">
$(document).ready(function(){

	$("span#date_next_certifcation").click(function() {

		check = confirm("<?php echo __('Do you want to use this date as start for certificate periode?',true);?>");

		if (check == false) {
			return false;
		}

		$("#<?php echo $DateID;?>").val($(this).text());
	});


});
</script>
